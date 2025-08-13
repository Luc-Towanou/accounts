<?php 
namespace App\Services;

use App\Models\RegleCalcul;
use App\Models\Tableau;
use App\Models\Variable;
use App\Models\SousVariable;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;

class RegleCalculService
{
   public function evaluer(string $expression): float
{
    $expression = ltrim($expression, '=');

    // 3 niveaux : tableau.variable.sousVariable
    $expression = preg_replace_callback(
        '/([\p{L}\d_]+)\.([\p{L}\d_]+)\.([\p{L}\d_]+)/u',
        function ($m) {
            [$full, $tbl, $var, $sub] = $m;

            $s = SousVariable::whereHas('variable', fn($q) =>
                    $q->where('nom', $var)
                      ->whereHas('tableau', fn($q2) =>
                          $q2->where('nom', $tbl)
                      )
                )
                ->where('nom', $sub)
                ->first();

            return $s->depense_reelle ?? 0;
        },
        $expression
    );

    // 2 niveaux : tableau.variable
    $expression = preg_replace_callback(
        '/([\p{L}\d_]+)\.([\p{L}\d_]+)/u',
        function ($m) {
            [$full, $tbl, $var] = $m;

            $v = Variable::where('nom', $var)
                ->whereHas('tableau', fn($q) =>
                    $q->where('nom', $tbl)
                )
                ->first();

            return $v->depense_reelle ?? 0;
        },
        $expression
    );

    // Sécurité : seuls chiffres, opérateurs, points et espaces
    if (!preg_match('#^[0-9+\-*/.() ]+$#', $expression)) {
        throw new Exception("Expression invalide ou non sécurisée : {$expression}");
    }

    return floatval(eval("return {$expression};"));
}
 

// public function evaluer(string $expression): float
// {
//     $original = $expression;
//     $expression = ltrim($expression, '=');
//     Log::debug("Expression originale : {$original}");

//     // Remplacer les sous-variables : tableau.variable.sousVariable
//     $expression = preg_replace_callback('/([a-zA-Z_][\w])\.([a-zA-Z_][\w])\.([a-zA-Z_][\w]*)/', function ($matches) {
//         [$full, $nomTableau, $nomVariable, $nomSousVariable] = $matches;

//         $sousVariable = SousVariable::whereHas('variable', function ($q) use ($nomVariable, $nomTableau) {
//             $q->where('nom', $nomVariable)
//               ->whereHas('tableau', function ($q2) use ($nomTableau) {
//                   $q2->where('nom', $nomTableau);
//               });
//         })->where('nom', $nomSousVariable)->first();

//         if (!$sousVariable) {
//             Log::warning("Sous-variable non trouvée : {$nomTableau}.{$nomVariable}.{$nomSousVariable}");
//             return 0;
//         }

//         return $sousVariable->depense_reelle ?? 0;
//     }, $expression);

//     // Remplacer les variables simples : tableau.variable
//     $expression = preg_replace_callback('/([a-zA-Z_][\w])\.([a-zA-Z_][\w])/', function ($matches) {
//         [$full, $nomTableau, $nomVariable] = $matches;

//         $variable = Variable::where('nom', $nomVariable)
//             ->whereHas('tableau', function ($q) use ($nomTableau) {
//                 $q->where('nom', $nomTableau);
//             })->first();

//         if (!$variable) {
//             Log::warning("Variable non trouvée : {$nomTableau}.{$nomVariable}");
//             return 0;
//         }

//         return $variable->depense_reelle ?? 0;
//     }, $expression);

//     Log::debug("Expression après remplacement : {$expression}");

//     // Sécurité : uniquement des chiffres et opérateurs mathématiques
//     if (!preg_match('/^[0-9\+\-\*\/\.\(\) ]+$/', $expression)) {
//         throw new Exception("Expression invalide ou non sécurisée : {$expression}");
//     }

//     try {
//         $result = eval("return {$expression};");
//         Log::debug("Résultat de l'expression : {$result}");
//         return floatval($result);
//     } catch (\Throwable $e) {
//         Log::error("Erreur d\'évaluation de l\'expression '{$expression}': " . $e->getMessage());
//         throw new Exception("Erreur d\'évaluation : " . $e->getMessage());
//     }
// }

public function validerExpression(string $expression): void
{
    $user = Auth::user();

    // Récupération de toutes les règles de l'utilisateur (pour vérifier les réutilisations)
    $variablesUtilisées = collect();
    $sousVariablesUtilisées = collect();

    RegleCalcul::whereHas('variable.tableau.moisComptable', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })->get()->each(function ($regle) use (&$variablesUtilisées, &$sousVariablesUtilisées) {
        preg_match_all('/([a-zA-Z_][\w]*)\.([a-zA-Z_][\w]*)(?:\.([a-zA-Z_][\w]*))?/', $regle->expression, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $variablesUtilisées->push($match[1] . '.' . $match[2]);
            if (isset($match[3])) {
                $sousVariablesUtilisées->push($match[1] . '.' . $match[2] . '.' . $match[3]);
            }
        }
    });

    // Analyse de l'expression courante
    preg_match_all('/([a-zA-Z_][\w]*)\.([a-zA-Z_][\w]*)(?:\.([a-zA-Z_][\w]*))?/', $expression, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $nomTableau = $match[1];
        $nomVariable = $match[2];
        $nomSousVariable = $match[3] ?? null;

        // Vérifie que le tableau appartient à l'utilisateur
        $tableau = Tableau::where('nom', $nomTableau)
            ->whereHas('moisComptable', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();

        if (!$tableau) {
            throw new Exception("Le tableau '{$nomTableau}' est introuvable ou ne vous appartient pas.");
        }

        // Vérifie que la variable existe dans ce tableau
        $variable = Variable::where('nom', $nomVariable)
            ->where('tableau_id', $tableau->id)
            ->first();

        if (!$variable) {
            throw new Exception("La variable '{$nomTableau}.{$nomVariable}' est introuvable.");
        }

        // Vérifie si cette variable est déjà utilisée ailleurs
        $cle = "{$nomTableau}.{$nomVariable}";
        if ($variablesUtilisées->contains($cle)) {
            throw new Exception("La variable '{$cle}' est déjà utilisée dans une autre règle de calcul.");
        }

        // Si sous-variable incluse, vérifie qu'elle existe et n’est pas utilisée ailleurs
        if ($nomSousVariable) {
            $sousVariable = SousVariable::where('nom', $nomSousVariable)
                ->where('variable_id', $variable->id)
                ->first();

            if (!$sousVariable) {
                throw new Exception("La sous-variable '{$cle}.{$nomSousVariable}' est introuvable.");
            }

            $cleSous = "{$cle}.{$nomSousVariable}";
            if ($sousVariablesUtilisées->contains($cleSous)) {
                throw new Exception("La sous-variable '{$cleSous}' est déjà utilisée dans une autre règle.");
            }
        }
    }
}

    public function analyser(RegleCalcul $regle): array
{
    $expression = $regle->expression;
    $variableCible = $regle->variable;
    $cleCible = "{$variableCible->tableau->nom}.{$variableCible->nom}";

    $variablesUtilisées    = collect();
    $sousVariablesUtilisées = collect();

    // Regex amélioré, Unicode, noms complets
    $pattern = '/([\p{L}\d_]+)\.([\p{L}\d_]+)(?:\.([\p{L}\d_]+))?/u';
    preg_match_all($pattern, $expression, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        [, $nomTableau, $nomVariable, $nomSousVariable] = $match;

        $tableau = Tableau::where('nom', $nomTableau)->first()
            ?? throw new Exception("Tableau '{$nomTableau}' introuvable.");

        $variable = Variable::where('nom', $nomVariable)
            ->where('tableau_id', $tableau->id)
            ->first()
            ?? throw new Exception("Variable '{$nomTableau}.{$nomVariable}' introuvable.");

        $variablesUtilisées->push("{$nomTableau}.{$nomVariable}");

        if ($nomSousVariable) {
            $sousVariable = SousVariable::where('nom', $nomSousVariable)
                ->where('variable_id', $variable->id)
                ->first()
                ?? throw new Exception("Sous-variable '{$nomTableau}.{$nomVariable}.{$nomSousVariable}' introuvable.");

            $sousVariablesUtilisées->push("{$nomTableau}.{$nomVariable}.{$nomSousVariable}");
        }
    }

    return [
        'variable_cible'            => $cleCible,
        'variables_utilisées'       => $variablesUtilisées->unique()->values()->all(),
        'sous_variables_utilisées'  => $sousVariablesUtilisées->unique()->values()->all(),
    ];
}


public function variableRegleCalcul(Variable $variable): ?Variable
{
    $cle = "{$variable->tableau->nom}.{$variable->nom}";
    $userId = $variable->tableau->moisComptable->user_id;

    $regles = RegleCalcul::whereHas('variable.tableau.moisComptable', function ($q) use ($userId) {
        $q->where('user_id', $userId);
    })->with('variable.tableau')->get();

    foreach ($regles as $regle) {
        if (preg_match("/\b" . preg_quote($cle) . "\b/", $regle->expression)) {
            return $regle->variable;
        }
    }

    return null;
}

public function sousVariableRegleCalcul(SousVariable $sousVariable): ?Variable
{
    $variable = $sousVariable->variable;
    $cle = "{$variable->tableau->nom}.{$variable->nom}.{$sousVariable->nom}";
    $userId = $variable->tableau->moisComptable->user_id;

    $regles = RegleCalcul::whereHas('variable.tableau.moisComptable', function ($q) use ($userId) {
        $q->where('user_id', $userId);
    })->with('variable.tableau')->get();

    foreach ($regles as $regle) {
        if (preg_match("/\b" . preg_quote($cle) . "\b/", $regle->expression)) {
            return $regle->variable;
        }
    }

    return null;
}


    // public function evaluer(string $expression): float
    // {
    //     // Nettoyage du début "=" si présent
    //     $expression = ltrim($expression, '=');

    //     // Remplacer les références par leurs valeurs
    //     $expression = preg_replace_callback('/variable\("(.+?)"\)/', function ($matches) {
    //         $nom = $matches[1];
    //         $variable = Variable::where('nom', $nom)->first();
    //         return $variable?->depense_reelle ?? 0;
    //     }, $expression);

    //     $expression = preg_replace_callback('/sous_variable\("(.+?)"\)/', function ($matches) {
    //         $nom = $matches[1];
    //         $sous = SousVariable::where('nom', $nom)->first();
    //         return $sous?->depense_reelle ?? 0;
    //     }, $expression);

    //     // Sécurité : empêcher toute fonction ou appel interdit
    //     if (preg_match('/[^0-9\+\-\*\/\.\(\) ]/', $expression)) {
    //         throw new \Exception("Expression invalide : $expression");
    //     }

    //     // Évaluer l'expression mathématique (⚠️ sécurisée)
    //     return eval("return {$expression};");
    // }
}
