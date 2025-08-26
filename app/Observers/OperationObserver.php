<?php

namespace App\Observers;

use App\Models\Operation;
use App\Models\SousVariable;
use App\Models\Tableau;
use App\Models\Variable;
use App\Services\ReglesCalculService;
use Illuminate\Support\Facades\Log;

class OperationObserver
{
    protected $regleService;

    public function __construct()
    {
        $this->regleService = new ReglesCalculService();
    }
    /**
     * Handle the Operation "created" event.
     */
    public function created(Operation $operation): void
    {
        //
        
            $this->recalculerImpact($operation);
    }

    /**
     * Handle the Operation "updated" event.
     */
    public function updated(Operation $operation): void
    {
        //
        // 🔹 Cas 1 : déplacement (sous_variable ou variable changée)
    if ($operation->isDirty('sous_variable_id') || $operation->isDirty('variable_id')) {
        // Anciennes valeurs
        $oldSousVarId = $operation->getOriginal('sous_variable_id');
        $oldVarId     = $operation->getOriginal('variable_id');

        // Recalculer l’ancien parent (si existant)
        if ($oldSousVarId) {
            $oldOperation = clone $operation;
            $oldOperation->sous_variable_id = $oldSousVarId;
            $oldOperation->variable_id = null;
            $this->recalculerImpact($oldOperation);
        } elseif ($oldVarId) {
            $oldOperation = clone $operation;
            $oldOperation->variable_id = $oldVarId;
            $oldOperation->sous_variable_id = null;
            $this->recalculerImpact($oldOperation);
        }

        // Recalculer la nouvelle cible (logique déjà existante)
        $this->recalculerImpact($operation);
    } else {
        // 🔹 Cas 2 : simple mise à jour (montant, description…)
        $this->recalculerImpact($operation);
    }

        // $this->recalculerImpact($operation);
    }

    /**
     * Handle the Operation "deleted" event.
     */
    public function deleted(Operation $operation): void
    {
        //
            $this->recalculerImpact($operation);
    }

    /**
     * Handle the Operation "restored" event.
     */
    public function restored(Operation $operation): void
    {
        //
            $this->recalculerImpact($operation);
    }

    /**
     * Handle the Operation "force deleted" event.
     */
    public function forceDeleted(Operation $operation): void
    {
        //
            $this->recalculerImpact($operation);
    } 

    /**
     * Recalcul en cascade suite à une opération
     */
    protected function recalculerImpact(Operation $operation)
    {
        $impactIds = [];
        $idsRecalcules = [];
        $variable = null;

        // 1️⃣ Cible directe
        if ($operation->sous_variable_id) {
            $sousVariable = $operation->sousVariable;

            if (!$sousVariable->calcule) {
                $sousVariable->depense_reelle = $sousVariable->operations()->sum('montant');
                $sousVariable->save();
            }
            $impactIds[] = $sousVariable->id;
            $variable = $sousVariable->variable;
        }
        elseif ($operation->variable_id) {
            $variable = $operation->variable;
             if ($variable->type === 'sous-tableau'){
                return; //une operation ne peut directement être lier à une variale de type 'sous-tableau'
             }

            if (!$variable->calcule) {
                $variable->depense_reelle = $variable->operations()->sum('montant');
                $variable->save();
            }
        } else {
            return; // Rien à recalculer
        }

       try {
            // 2️⃣ Récupérer les dépendances
            $this->recalculerDependances($impactIds);
        } catch (\Throwable $e) {
            Log::error("Erreur lors du recalcul des dépendances : " . $e->getMessage());
        }

        // Récupérer les variables recalculées
        // $variablesParentes = Variable::whereIn('id', $idsRecalcules)->get();


        // 3️⃣ Mise à jour du parent variable
        if ($variable && !$variable->calcule && $variable->sousVariables()->exists()) {
            $variable->depense_reelle = $variable->sousVariables()->sum('depense_reelle');
            $variable->save();
        } 

        // 4️⃣ Mise à jour tableau et mois
        if ($variable) {
            $tableau = $variable->tableau;
            // dd($tableau);
            $tableau->depense_reelle = $tableau->variables()->sum('depense_reelle');

           

            $tableau->save();
            // Recalcul au niveau mois comptable
            $mois = $tableau->moisComptable;

            $mois->depense_reelle = $mois->tableaux()
                ->where('nature', 'sortie')
                ->sum('depense_reelle');

            $mois->gains_reelle = $mois->tableaux()
                ->where('nature', 'entree')
                ->sum('depense_reelle');

            $mois->montant_net = $mois->gains_reelle - $mois->depense_reelle;

            $mois->save();
        }
       
    
    }

    /**
     * Recalcul des dépendances via règles
     */
    protected function recalculerDependances(array $idsSousVar)
{
    $aRecalculerSousVars = [];
    $aRecalculerVars = [];
    $aRecalculerTableaux = [];
    $aRecalculerMois = [];

    // 🔹 1. Sous-variables calculées
    $sousVariables = SousVariable::where('calcule', true)
        ->whereHas('regleCalcul')
        ->get();

    foreach ($sousVariables as $sous) {
        $expression = optional($sous->regleCalcul)->expression;
        if (!$expression) continue;

        $deps = $this->regleService->getDependances($expression);
        if (array_intersect($idsSousVar, $deps)) {
            try {
                $sous->depense_reelle = $this->regleService->evaluer($expression);
                $sous->save();
                $aRecalculerSousVars[] = $sous->id;

                // ⚡ remonter vers la variable associée
                if ($sous->variable) {
                    $aRecalculerVars[] = $sous->variable->id;
                }
            } catch (\Exception $e) {
                Log::error("Erreur règle sous-var ID {$sous->id} [{$sous->nom}] : " . $e->getMessage());
            }
        }
    }

    // 🔹 2. Variables calculées
    $variables = Variable::where('calcule', true)
        ->whereHas('regleCalcul')
        ->get();

    foreach ($variables as $var) {
        $expression = optional($var->regleCalcul)->expression;
        if (!$expression) continue;

        $deps = $this->regleService->getDependances($expression);
        if (array_intersect(array_merge($idsSousVar, $aRecalculerSousVars), $deps)) {
            try {
                $var->depense_reelle = $this->regleService->evaluer($expression);
                $var->save();
                $aRecalculerVars[] = $var->id;

                // ⚡ remonter vers le tableau associé
                if ($var->tableau) {
                    $aRecalculerTableaux[] = $var->tableau->id;
                }
            } catch (\Exception $e) {
                Log::error("Erreur règle var ID {$var->id} [{$var->nom}] : " . $e->getMessage());
            }
        }
    }

    // 🔹 3. Tableaux impactés
    $tableaux = Tableau::whereIn('id', $aRecalculerTableaux)->get();
    foreach ($tableaux as $tableau) {
        $tableau->depense_reelle = $tableau->variables()->sum('depense_reelle');
        $tableau->save();

        // ⚡ remonter vers le mois comptable
        if ($tableau->moisComptable) {
            $aRecalculerMois[] = $tableau->moisComptable->id;
        }
    }

    // 🔹 4. Mois comptables impactés
    $moisComptables = \App\Models\MoisComptable::whereIn('id', $aRecalculerMois)->get();
    foreach ($moisComptables as $mois) {
        $mois->depense_reelle = $mois->tableaux()->where('nature', 'sortie')->sum('depense_reelle');
        $mois->gains_reelle   = $mois->tableaux()->where('nature', 'entree')->sum('depense_reelle');
        $mois->montant_net    = $mois->gains_reelle - $mois->depense_reelle;
        $mois->save();
    }

    return array_merge($aRecalculerSousVars, $aRecalculerVars, $aRecalculerTableaux, $aRecalculerMois);
}

    // protected function recalculerDependances(array $idsSousVar)
    // {
    //     $aRecalculerSousVars = [];
    //     $aRecalculerVars = [];

    //     // Sous-variables calculées
    //     $sousVariables = SousVariable::where('calcule', true)
    //         // ->whereNotNull('regle_calcul')
    //         ->whereHas(relation: 'regleCalcul')
    //         ->get();

    //     foreach ($sousVariables as $sous) {
    //         // $deps = $this->regleService->getDependances($sous->regle_calcul);
    //         $expression = optional($sous->regleCalcul)->expression;
    //         if (!$expression) continue;

    //         $deps = $this->regleService->getDependances($expression);
    //         if (array_intersect($idsSousVar, $deps)) {
    //             try {
    //                 $sous->depense_reelle = $this->regleService->evaluer($sous->regle_calcul);
    //                 $sous->save();
    //                 $aRecalculerSousVars[] = $sous->id;
    //             } catch (\Exception $e) {
    //                 Log::error("Erreur règle sous-var ID {$sous->id} [{$sous->nom}] : " . $e->getMessage());
    //             }
    //         }
    //     }

    //     // Variables calculées
    //     $variables = Variable::where('calcule', true)
    //         ->whereHas('regleCalcul')
    //         ->get();

    //     foreach ($variables as $var) {
    //         $expression = optional($var->regleCalcul)->expression;
    //         if (!$expression) continue;

    //         $deps = $this->regleService->getDependances($expression);
    //         if (array_intersect(array_merge($idsSousVar, $aRecalculerSousVars), $deps)) {
    //             try {
    //                 $var->depense_reelle = $this->regleService->evaluer($expression);
    //                 $var->save();
    //                 // dd($var);
    //                 $aRecalculerVars[] = $var->id;
    //             } catch (\Exception $e) {
    //                 Log::error("Erreur règle var ID {$var->id} [{$var->nom}] : " . $e->getMessage());
    //             }
    //         }
    //     }
    //     return array_merge( $aRecalculerVars);
    //             // return array_merge($aRecalculerSousVars, $aRecalculerVars);
    // }


     // if ($tableau->nature === 'sortie') {
            //     $tableau->depense_reelle = $total;
            //     $tableau->gains_reelle   = 0;
            // } else {
            //     $tableau->gains_reelle   = $total;
            //     $tableau->depense_reelle = 0;
            // }
    
    // protected function recalculerImpact(Operation $operation)
    // {
    //     $impactIds = [];

    //     // 1️⃣ Cible directe
    //     if ($operation->sous_variable_id) {
    //         $sousVariable = $operation->sousVariable;

    //         if (!$sousVariable->calcule) {
    //             $sousVariable->depense_reelle = $sousVariable->operations()->sum('montant');
    //             $sousVariable->save();
    //         }

    //         $impactIds[] = $sousVariable->id;
    //         $variable = $sousVariable->variable;
    //     }
    //     elseif ($operation->variable_id) {
    //         $variable = $operation->variable;

    //         if (!$variable->calcule) {
    //             $variable->depense_reelle = $variable->operations()->sum('montant');
    //             $variable->save();
    //         }
    //     } else {
    //         return; // Aucun lien → on arrête
    //     }

    //     // 2️⃣ Recalcul des dépendances
    //     $this->recalculerDependances($impactIds);

    //     // 3️⃣ Mise à jour du parent
    //     if (isset($variable) && !$variable->calcule) {
    //         $variable->depense_reelle = $variable->sousVariables()->sum('depense_reelle');
    //         $variable->save();
    //     }

    //     if (isset($variable)) {
    //         $tableau = $variable->tableau;

    //         // Recalcul des totaux pour ce tableau
    //         $tableau->depense_reelle = $tableau->variables()
    //             ->whereHas('operations', function ($q) {
    //                 $q->where('nature', 'sortie');
    //             })
    //             ->sum('depense_reelle');

    //         $tableau->gains_reelle = $tableau->variables()
    //             ->whereHas('operations', function ($q) {
    //                 $q->where('nature', 'entree');
    //             })
    //             ->sum('depense_reelle');

    //         $tableau->save();

    //         // Recalcul du mois
    //         $mois = $tableau->moisComptable;

    //         $mois->depense_reelle = $mois->tableaux()
    //             ->where('nature', 'sortie')
    //             ->sum('depense_reelle');

    //         $mois->gains_reelle = $mois->tableaux()
    //             ->where('nature', 'entree')
    //             ->sum('depense_reelle');

    //         // Montant net = gains - dépenses
    //         $mois->montant_net = $mois->gains_reelle - $mois->depense_reelle;

    //         $mois->save();
    //     }


        
    // }

    // protected function recalculerDependances(array $idsSousVar)
    // {
    //     $aRecalculerSousVars = [];
    //     $aRecalculerVars = [];

    //     // Sous-variables calculées
    //     $sousVariables = SousVariable::where('calcule', true)
    //         ->whereNotNull('regle_calcul')
    //         ->get();

    //     foreach ($sousVariables as $sous) {
    //         $deps = $this->regleService->getDependances($sous->regle_calcul);
    //         if (array_intersect($idsSousVar, $deps)) {
    //             try {
    //                 $sous->depense_reelle = $this->regleService->evaluer($sous->regle_calcul);
    //                 $sous->save();
    //                 $aRecalculerSousVars[] = $sous->id;
    //             } catch (\Exception $e) {
    //                 Log::error("Erreur règle sous-var [{$sous->nom}] : " . $e->getMessage());
    //             }
    //         }
    //     }

    //     // Variables calculées
    //     $variables = Variable::where('calcule', true)
    //         ->whereNotNull('regle_calcul')
    //         ->get();

    //     foreach ($variables as $var) {
    //         $deps = $this->regleService->getDependances($var->regleCalcul->expression ?? '');
    //         if (array_intersect(array_merge($idsSousVar, $aRecalculerSousVars), $deps)) {
    //             try {
    //                 $var->depense_reelle = $this->regleService->evaluer($var->regleCalcul->expression);
    //                 $var->save();
    //                 $aRecalculerVars[] = $var->id;
    //             } catch (\Exception $e) {
    //                 Log::error("Erreur règle var [{$var->nom}] : " . $e->getMessage());
    //             }
    //         }
    //     }
    // }


    // if (isset($variable)) {
        //     $tableau = $variable->tableau;
        //     $tableau->depense_reelle = $tableau->variables()->sum('depense_reelle');
        //     $tableau->save();

        //     $mois = $tableau->moisComptable;
        //     $mois->depense_reelle = $mois->tableaux()->sum('depense_reelle');
        //     $mois->save();
        // }

    // protected function recalculerTotaux(Operation $operation)
    // {
    //     // 1. Traitement selon le lien de l’opération
    //     if ($operation->sous_variable_id) {
    //         $sousVariable = $operation->sousVariable;

    //         // Si non calculée → somme des opérations
    //         if (!$sousVariable->calcule) {
    //             $sousVariable->depense_reelle = $sousVariable->operations()->sum('montant');
    //             $sousVariable->save();
    //         }

    //         // Recalcul de la variable parente (type sous-tableau)
    //         $variable = $sousVariable->variable;
    //         if (!$variable->calcule) { 
    //             $variable->depense_reelle = $variable->sousVariables()->sum('depense_reelle');
    //             // dd($variable);
    //             $variable->save(); 
    //         }

    //         $tableau = $variable->tableau;
    //     }
    //     elseif ($operation->variable_id) {
    //         $variable = $operation->variable;

    //         // Si non calculée → somme directe
    //         if (!$variable->calcule) {
    //             $variable->depense_reelle = $variable->operations()->sum('montant');
    //             $variable->save();
    //         }

    //         $tableau = $variable->tableau;
    //     } else {
    //         return; // Sécurité : l’opération ne cible rien
    //     }

    //     // 2. Recalcul du tableau parent
    //     $tableau->depense_reelle = $tableau->variables()->sum('depense_reelle');
    //     $tableau->save();

    //     // 3. Recalcul du mois
    //     $mois = $tableau->moisComptable;
    //     $mois->depense_reelle = $mois->tableaux()->sum('depense_reelle');
    //     $mois->save();

    //     // 4. Recalcul de toutes les entités "calculées"
    //     $this->recalculerToutesLesEntitesCalculees();
    // }

    // protected function recalculerToutesLesEntitesCalculees()
    // {
    //     // 1. Variables calculées
    //     $variables = Variable::where('calcule', true)->whereNotNull('regle_calcul')->get();

    //     foreach ($variables as $var) {
    //         try {
    //             $regle = $var->regleCalcul;
    //             $var->depense_reelle = $this->regleService->evaluer($regle->expression);
    //             $var->save();
    //         } catch (\Exception $e) {
    //             Log::error("Erreur règle var [{$var->nom}] : " . $e->getMessage());
    //         }
    //     }

    //     // 2. Sous-variables calculées
    //     $sousVariables = SousVariable::where('calcule', true)->whereNotNull('regle_calcul')->get();

    //     foreach ($sousVariables as $sous) {
    //         try {
    //             $sous->depense_reelle = $this->regleService->evaluer($sous->regle_calcul);
    //             $sous->save();
    //         } catch (\Exception $e) {
    //             Log::error("Erreur règle sous-var [{$sous->nom}] : " . $e->getMessage());
    //         }
    //     }
    // }


    
} 
