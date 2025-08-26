<?php

namespace App\Services;

use App\Models\Variable;
use App\Models\SousVariable;
use App\Models\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class VariableService
{
    /**
     * Calcule le montant total d'une variable selon une période donnée
     */
    public function calculerMontant(Variable $variable, Carbon $dateDebut, Carbon $dateFin): float
    {
        switch ($variable->type) {
            case 'simple':
                return $this->calculerVariableSimple($variable, $dateDebut, $dateFin);

            case 'sous-tableau':
                return $this->calculerSousTableau($variable, $dateDebut, $dateFin);

            default:
                return 0.0;
        }
    }

    /**
     * Variable simple : calcul direct ou via règle
     */
    protected function calculerVariableSimple(Variable $variable, Carbon $dateDebut, Carbon $dateFin): float
    {
        if ($variable->calcule) {
            // Variable calculée → via règle
            return $this->evaluerRegle($variable, $dateDebut, $dateFin);
        } else {
            // Variable simple non calculée → somme directe des opérations liées
            return Operation::where('variable_id', $variable->id)
                            ->whereBetween('date', [$dateDebut, $dateFin])
                            ->sum('montant');
        }
    }

    /**
     * Variable de type "sous-tableau"
     */
    protected function calculerSousTableau(Variable $variable, Carbon $dateDebut, Carbon $dateFin): float
    {
        $sousVariables = $variable->sousVariables; // relation hasMany

        $total = 0.0;
        foreach ($sousVariables as $sv) {
            $total += Operation::where('sous_variable_id', $sv->id)
                ->whereBetween('date', [$dateDebut, $dateFin])
                ->sum('montant');
        }

        return $total;
    }

    /**
     * Évaluation de la règle de calcul d'une variable
     */
    protected function evaluerRegle(Variable $variable, Carbon $dateDebut, Carbon $dateFin): float
{
    $regle = $variable->regleCalcul;
    if (!$regle || empty($regle->expression)) {
        return 0.0;
    }

    $expression = $regle->expression;
    $variables = [];

    // 1️⃣ Corriger les coefficients devant parenthèse ex: "2(" → "2*("
    $expression = preg_replace('/(\d)\s*\(/', '$1*(', $expression);

    // 2️⃣ Trouver toutes les références du type "nom.id"
    preg_match_all('/([a-zA-Z0-9_]+)\.(\d+)/', $expression, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $fullRef   = $match[0];   // ex: "transport.5"
        $sousVarId = intval($match[2]); // ex: 5

        // Somme des opérations sur la période
        $valeur = Operation::where('sous_variable_id', $sousVarId)
            ->whereBetween('date', [$dateDebut, $dateFin])
            ->sum('montant');

        // ⚠️ Symfony n’accepte pas les "." → on les remplace par "_"
        $safeRef = str_replace('.', '_', $fullRef);

        // Remplacer dans l’expression
        $expression = str_replace($fullRef, $safeRef, $expression);

        // Ajouter la valeur dans les variables connues
        $variables[$safeRef] = $valeur;
    }

    // 3️⃣ Évaluer avec ExpressionLanguage
    $language = new ExpressionLanguage();

    try {
        return $language->evaluate($expression, $variables);
    } catch (\Throwable $e) {
        // En cas d'expression invalide
        return 0.0;
    }
}
}
