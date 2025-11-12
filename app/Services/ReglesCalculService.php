<?php 
namespace App\Services;

use App\Models\RegleCalcul;
use App\Models\SousVariable;
use App\Models\Variable;
use Illuminate\Support\Facades\Auth;
use Exception;

class ReglesCalculService
{
    /**
     * Évalue une expression contenant NomSousVariable.ID
     */
    public function evaluer(string $expression): float
    {
        $userId = Auth::id();
        $expression = ltrim($expression, '=');

        // Remplacer chaque NomSousVariable.ID par sa valeur
        $expression = preg_replace_callback(
            '/([a-zA-Z_][\w]*)\.(\d+)/',
            function ($matches) use ($userId) {
                $nom = $matches[1];
                $id  = (int) $matches[2];

                $sousVariable = SousVariable::where('id', $id)
                    ->where('user_id', $userId)
                    ->first();

                if (!$sousVariable) {
                    throw new Exception("La sous-variable '{$nom}.{$id}' est introuvable ou ne vous appartient pas.");
                }

                // Vérifie cohérence du nom (optionnel mais sécurisant)
                if ($sousVariable->nom !== $nom) {
                    throw new Exception("Le nom '{$nom}' ne correspond pas à la sous-variable #{$id}.");
                }

                return $sousVariable->depense_reelle ?? 0;
            },
            $expression
        );

        // Sécurité : seuls chiffres, opérateurs, points et espaces
        if (!preg_match('#^[0-9+\-*/.() ]+$#', $expression)) {
            throw new Exception("Expression invalide ou non sécurisée : {$expression}");
        }

        return floatval(eval("return {$expression};"));
    }


    /**
     * Valide une expression avant enregistrement
     * + vérifie que chaque sous-variable utilisée n'est pas déjà dans une autre règle
     */
    public function validerExpression(string $expression): void
    {
        $userId = Auth::id();

        preg_match_all('/([a-zA-Z_][\w]*)\.(\d+)/', $expression, $matches, PREG_SET_ORDER);

        $idsUtilises = [];

        foreach ($matches as $match) {
            $nom = $match[1];
            $id  = (int) $match[2];

            if (in_array($id, $idsUtilises)) {
                continue; // éviter doublons
            }
            $idsUtilises[] = $id;

            $sousVariable = SousVariable::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$sousVariable) {
                throw new Exception("La sous-variable '{$nom}.{$id}' est introuvable ou ne vous appartient pas.");
            }

            if ($sousVariable->nom !== $nom) {
                throw new Exception("Le nom '{$nom}' ne correspond pas à la sous-variable #{$id}.");
            }

            // 🔍 Vérifie si cette sous-variable est déjà utilisée dans une autre règle
            $variableUtilisatrice = $this->sousVariableRegleCalcul($sousVariable);
            if ($variableUtilisatrice !== null) {
                throw new Exception("La sous-variable '{$nom}.{$id}' est déjà utilisée dans la règle de calcul de la variable #{$variableUtilisatrice}.");
            }
        }
    }

    

    /**
     * Valide une expression avant enregistrement
     */

    /**
     * Analyse une règle de calcul : retourne la cible et toutes les sous-variables utilisées
     */
    public function analyser(RegleCalcul $regle): array
    {
        preg_match_all('/([a-zA-Z_][\w]*)\.(\d+)/', $regle->expression, $matches, PREG_SET_ORDER);

        $ids = [];
        foreach ($matches as $match) {
            $ids[] = (int) $match[2];
        }

        $ids = array_unique($ids);

        return [
            'variable_cible'           => $regle->variable_id,
            'sous_variables_utilisées' => $ids
        ];
    }


    /**
     * Vérifie si une variable est déjà utilisée dans une autre règle
     */
    

    /**
     * Vérifie si une sous-variable est déjà utilisée dans une autre règle
     */
    public function sousVariableRegleCalcul(SousVariable $sousVariable): ?int
    {
        $userId = $sousVariable->user_id;
        $id = $sousVariable->id;

        $regles = RegleCalcul::whereHas('variable', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->get();

        foreach ($regles as $regle) {
            if (preg_match("/\b{$id}\b/", $regle->expression)) {
                return $regle->variable_id;
            }
        }

        return null;
    }

    public function getDependances(string $expression): array
    {
        preg_match_all('/[a-zA-Z_][\w]*\.(\d+)/', $expression, $matches);
        $ids = array_map('intval', $matches[1] ?? []);
        return array_unique($ids);
    }

    // public function variableRegleCalcul(Variable $variable): ?Variable
    // {
    //     $userId = $variable->user_id;
    //     $cle = "{$variable->tableau->nom}.{$variable->nom}";
    //     $userId = $variable->tableau->moisComptable->user_id;

    //     $regles = RegleCalcul::whereHas('variable.tableau.moisComptable', function ($q) use ($userId) {
    //         $q->where('user_id', $userId);
    //     })->with('variable.tableau')->get();

    //     foreach ($regles as $regle) {
    //         if (preg_match("/\b" . preg_quote($cle) . "\b/", $regle->expression)) {
    //             return $regle->variable;
    //         }
    //     }

    //     return null;
    // }
    
    // public function validerExpression(string $expression): void
    // {
    //     $userId = Auth::id();

    //     preg_match_all('/([a-zA-Z_][\w]*)\.(\d+)/', $expression, $matches, PREG_SET_ORDER);

    //     $idsUtilises = [];

    //     foreach ($matches as $match) {
    //         $nom = $match[1];
    //         $id  = (int) $match[2];

    //         if (in_array($id, $idsUtilises)) {
    //             continue; // éviter les doublons
    //         }
    //         $idsUtilises[] = $id;

    //         $sousVariable = SousVariable::where('id', $id)
    //             ->where('user_id', $userId)
    //             ->first();

    //         if (!$sousVariable) {
    //             throw new Exception("La sous-variable '{$nom}.{$id}' est introuvable ou ne vous appartient pas.");
    //         }

    //         if ($sousVariable->nom !== $nom) {
    //             throw new Exception("Le nom '{$nom}' ne correspond pas à la sous-variable #{$id}.");
    //         }
    //     }
    // }
}
