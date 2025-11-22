<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Operation;
use App\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OperationController extends Controller
{
    //
//    public function index()
//     {
//         $user = Auth::user();

//         // Filtrer directement sur user_id dans variables ou sous_variables
//         $sorties = Operation::with(['variable', 'sousVariable'])
//             ->where('nature', 'sortie')
//             ->where(function($q) use ($user) {
//                 $q->whereHas('variable', fn($qb) =>
//                         $qb->where('user_id', $user->id)
//                     )
//                 ->orWhereHas('sousVariable', fn($qb) =>
//                         $qb->where('user_id', $user->id)
//                     );
//             })
//             ->get();

//         $entrees = Operation::with(['variable', 'sousVariable'])
//             ->where('nature', 'entree')
//             ->where(function($q) use ($user) {
//                 $q->whereHas('variable', fn($qb) =>
//                         $qb->where('user_id', $user->id)
//                     )
//                 ->orWhereHas('sousVariable', fn($qb) =>
//                         $qb->where('user_id', $user->id)
//                     );
//             })
//             ->get();

//         return response()->json([
//             'message' => 'Liste chargée',
//             'sorties' => $sorties,
//             'entrees' => $entrees,
//         ]);
//     }


 

//     // index($variableId) — Voir les opérations d’une variable
//     public function indexVariable($variableId)
//     {
//         $variable = Variable::with('operations')
//                             ->findOrFail($variableId);

//         return response()->json([
//             'variable' => $variable->nom,
//             'budget_prevu' => $variable->budget_prevu,
//             'depense_reelle' => $variable->depense_reelle,
//             'operations' => $variable->operations
//         ]);
//     }

//     // Lister les opérations par variable
//     public function indexByVariable($variableId)
//     {
//         $user = Auth::user();
//         $variable = Variable::where('id', $variableId)
//                             ->where('user_id', $user->id)
//                             ->first();
//         if($variable->type === 'sous-tableau') {
//                     $operation = $variable->sousVariables()->operations()
//                                                             ->with('sousVariavle');
//         } elseif ($variable->type === 'simple') {
//                     $operation = $variable->operations()
//                                         ->with('variable');
//         }
        
//         return response()->json([ 'message' =>'Voici les operations des cette variables ',
//                                     'operations' => $operation, ]);

//     }

    // // Lister les opérations par sous-variable
    // public function indexBySousVariable($sousVariableId)
    // {
    //     return Operation::where('sous_variable_id', $sousVariableId)->get();
    // }

   
    // 🔹 Lister toutes les opérations de l'utilisateur
    public function index()
    {
        $user = Auth::user();

        $sorties = Operation::with('categorie.parent')
            ->where('nature', 'sortie')
            ->whereHas('categorie', fn($q) => $q->where('user_id', $user->id))
            ->get()
            ->map(fn($op) => $this->formatOperation($op));

        $entrees = Operation::with('categorie.parent')
            ->where('nature', 'entree')
            ->whereHas('categorie', fn($q) => $q->where('user_id', $user->id))
            ->get()
            ->map(fn($op) => $this->formatOperation($op));

        return response()->json([
            'message' => 'Liste chargée',
            'sorties' => $sorties,
            'entrees' => $entrees,
        ]);
    }

    // 🔹 Lister les opérations pour une "variable" (catégorie niveau 1)
    public function indexVariable($categorieId)
    {
        $categorie = Categorie::with('enfants.operations')->findOrFail($categorieId);
        $user = Auth::user();

        if ($categorie->user_id !== $user->id) {
            return response()->json('Non autorisé', 401);
        }

        $operations = collect();
        if ($categorie->enfants->isNotEmpty()) {
            foreach ($categorie->enfants as $enfant) {
                $operations = $operations->merge($enfant->operations);
            }
        }

        return response()->json([
            'variable' => $categorie->nom,
            'budget_prevu' => $categorie->budget_prevu,
            'depense_reelle' => $categorie->depense_reelle,
            'operations' => $operations->map(fn($op) => $this->formatOperation($op)),
        ]);
    }

    // 🔹 Lister les opérations d'une catégorie "variable ou sous-variable"
    public function indexByVariable($categorieId)
    {
        $categorie = Categorie::with('operations', 'enfants.operations')->findOrFail($categorieId);
        $user = Auth::user();

        if ($categorie->user_id !== $user->id) {
            return response()->json('Non autorisé', 401);
        }

        $operations = collect();

        if ($categorie->enfants->isNotEmpty()) {
            foreach ($categorie->enfants as $enfant) {
                $operations = $operations->merge($enfant->operations);
            }
        } else {
            $operations = $categorie->operations;
        }

        return response()->json([
            'message' => 'Voici les opérations de cette variable',
            'operations' => $operations->map(fn($op) => $this->formatOperation($op)),
        ]);
    }

    // 🔹 Helper pour reconstruire le format "variable / sousVariable"
    private function formatOperation(Operation $op)
    {
        $data = [
            'id'          => $op->id,
            'montant'     => $op->montant,
            'nature'      => $op->nature,
            'description' => $op->description,
            'date'        => $op->date,
            'variable'    => null,
            'sousVariable'=> null,
        ];

        if ($op->categorie->parent_id === null) {
            // Catégorie racine → variable
            $data['variable'] = $op->categorie;
        } elseif (!$op->categorie->enfants()->exists()) {
            // Feuille → sousVariable
            $data['sousVariable'] = $op->categorie;
            $data['variable'] = $op->categorie->parent;
        } else {
            // Intermédiaire → variable
            $data['variable'] = $op->categorie;
        }

        return $data;
    }
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'montant'           => 'required|numeric|min:0',
    //         'nature'            => 'required|in:entree,sortie',
    //         'description'       => 'nullable|string',
    //         'date'              => 'nullable|date',
    //         'variable_id'       => 'nullable|exists:variables,id',
    //         'sous_variable_id'  => 'nullable|exists:sous_variables,id',
    //     ]);

    //     // 1.– Business validations hors transaction
    //     if (empty($validated['variable_id']) && empty($validated['sous_variable_id'])) {
    //         return response()->json([
    //             'error' => "L'opération doit être liée à une variable ou une sous-variable."
    //         ], 422);
    //     }

    //     if (! empty($validated['variable_id']) && ! empty($validated['sous_variable_id'])) {
    //         return response()->json([
    //             'error' => "Une opération ne peut pas appartenir à la fois à une variable et à une sous-variable."
    //         ], 422);
    //     }

    //     if (! empty($validated['variable_id'])) {
    //         $variable = Variable::findOrFail($validated['variable_id']);
    //         if ($variable->type === 'sous-tableau') {
    //             return response()->json([
    //                 'error' => "L'opération ne peut être directement relié à la variable elle même. Choisissez plutot une sous-variable."
    //             ], 422);
    //         }
    //     }

    //     // 2.– Transaction : création pure
    //     try {
    //         $operation = DB::transaction(function() use ($validated) {
    //             return Operation::create([
    //                 'montant'           => $validated['montant'],
    //                 'description'       => $validated['description'] ?? null,
    //                 'date'              => $validated['date'] ?? now(),
    //                 'nature'            => $validated['nature'],
    //                 'variable_id'       => $validated['variable_id'] ?? null,
    //                 'sous_variable_id'  => $validated['sous_variable_id'] ?? null,
    //             ]);
    //         });
    //     } catch (\Throwable $e) {
    //         Log::error("Erreur lors de la création de l'opération : {$e->getMessage()}");
    //         return response()->json([
    //             'error' => "Une erreur est survenue lors de la création de l'opération."
    //         ], 500);
    //     }

    //     // 3.– Retour au client, hors transaction
    //     if (! empty($validated['variable_id'])) {
    //         return response()->json($operation->load('variable'), 201);
    //     }

    //     return response()->json($operation->load('sousVariable'), 201);
    // }

//     public function store(Request $request)
//     {
//         // ✅ On garde le même format que le front
//         $validated = $request->validate([
//             'montant'           => 'required|numeric|min:0',
//             'nature'            => 'required|in:entree,sortie',
//             'description'       => 'nullable|string',
//             'date'              => 'nullable|date',
//             'variable_id'       => 'nullable|exists:categories,id',
//             'sous_variable_id'  => 'nullable|exists:categories,id',
//         ]);

//         $user = Auth::user();

//         // 1️⃣ Vérifications métiers
//         if (empty($validated['variable_id']) && empty($validated['sous_variable_id'])) {
//             return response()->json([
//                 'error' => "L'opération doit être liée à une variable ou une sous-variable."
//             ], 422);
//         }

//         if (! empty($validated['variable_id']) && ! empty($validated['sous_variable_id'])) {
//             return response()->json([
//                 'error' => "Une opération ne peut pas appartenir à la fois à une variable et à une sous-variable."
//             ], 422);
//         }

//         // 🧭 Détermination de la catégorie cible
//         $categorie = null;
//         if (!empty($validated['sous_variable_id'])) {
//             // correspond à une catégorie "feuille" de niveau 3 (ex-sous-variable)
//             $categorie = Categorie::where('id', $validated['sous_variable_id'])
//                                     ->where('niveau', 3)
//                                     ->first();
//             $niveauAttendu = 3;
//         } elseif (!empty($validated['variable_id'])) {
//             // correspond à une catégorie de niveau 2 (ex-variable simple)
//             $categorie = Categorie::where('id',$validated['variable_id'])
//                                     ->where('niveau', 2)
//                                     ->first();
//             $niveauAttendu = 2;
//         }

//         if (!$categorie) {
//             return response()->json([
//                 'error' => "La variable ou sous-variable spécifiée est introuvable."
//             ], 404);
//         }

//         // 🔒 Vérifie que la catégorie appartient bien à l'utilisateur
//         if ($categorie->user_id !== $user->id) {
//             return response()->json([
//                 'error' => "Vous n'êtes pas autorisé à créer une opération sur cette variable."
//             ], 403);
//         }

//         // 🚫 Empêche d’ajouter une opération sur une catégorie parent
//         if ($categorie->enfants()->exists()) {
//             return response()->json([
//                 'error' => "Les opérations doivent être enregistrées uniquement sur les sous-variables finales (catégories sans enfants)."
//             ], 422);
//         }

//         // 2️⃣ Création transactionnelle
//         try {
//              Log::info('categorie: ' . $categorie->id);
//             $operation = DB::transaction(function () use ($validated, $user, $categorie) {
//                 $operation = Operation::create([
//                     'montant'       => $validated['montant'],
//                     'description'   => $validated['description'] ?? null,
//                     'date'          => $validated['date'] ?? now(),
//                     'nature'        => $validated['nature'],
//                     'categorie_id'  => $categorie->id,
//                     'user_id'       => $user->id,
//                 ]);

                

//                 return $operation;
//             });
//         } catch (\Throwable $e) {
//             Log::error("Erreur lors de la création de l'opération : {$e->getMessage()}");
//             return response()->json([
//                 'error' => "Une erreur est survenue lors de la création de l'opération."
//             ], 500);
//         }
//         Log::info('Operation: ' . $operation);
//         // 3️⃣ Retour au client (même format)
//         if (!empty($validated['variable_id'])) {
//             return response()->json([
//                 'message' => "Opération ajoutée avec succès à la variable.",
//                 'operation' => $operation->load('categorie'),
//             ], 201);
//         }

//         return response()->json([
//             'message' => "Opération ajoutée avec succès à la sous-variable.",
//             'operation' => $operation->load('categorie'),
//         ], 201);
// }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'montant'           => 'required|numeric|min:0',
            'nature'            => 'required|in:entree,sortie',
            'description'       => 'nullable|string',
            'date'              => 'nullable|date',
            'variable_id'       => 'nullable|exists:categories,id',
            'sous_variable_id'  => 'nullable|exists:categories,id',
        ]);

        $user = Auth::user();

        // --- Vérifications métiers de base ---
        if (empty($validated['variable_id']) && empty($validated['sous_variable_id'])) {
            return response()->json(['error' => "L'opération doit être liée à une variable ou une sous-variable."], 422);
        }

        if (!empty($validated['variable_id']) && !empty($validated['sous_variable_id'])) {
            return response()->json(['error' => "Une opération ne peut pas appartenir à la fois à une variable et une sous-variable."], 422);
        }

        // --- Détermination de la catégorie ciblée ---
        $categorieId = $validated['sous_variable_id'] ?? $validated['variable_id'];
        $categorie = Categorie::find($categorieId);

        if (!$categorie) {
            return response()->json(['error' => "Catégorie introuvable."], 404);
        }

        // Niveau attendu (pour cohérence)
        $niveauAttendu =  !empty($validated['sous_variable_id'] ?? null) ? 3 : 2; //validated['sous_variable_id'] ? 3 : 2;

        // --- Cas 1 : la catégorie appartient déjà à l'utilisateur ---
        if ($categorie->user_id === $user->id) {

            // Vérifie que c'est bien une feuille
            if ($categorie->enfants()->exists()) {
                return response()->json([
                    'error' => "Les opérations doivent être enregistrées uniquement sur les sous-catégories finales."
                ], 422);
            }

            $cibleCategorie = $categorie;
        }

        // --- Cas 2 : catégorie template à dupliquer ---
        // else {
        //     if (!$categorie->is_template) {
        //         return response()->json(['error' => "Vous n'êtes pas autorisé à utiliser cette catégorie."], 403);
        //     }

        //     // Vérifie que la catégorie template est bien une feuille
        //     if ($categorie->enfants()->exists()) {
        //         return response()->json([
        //             'error' => "Impossible d'ajouter une opération sur une catégorie template parent."
        //         ], 422);
        //     }

        //     // Récupère son mois comptable actif
        //     $moisComptable = $user->moisComptables()->latest()->first();
        //     if (!$moisComptable) {
        //         return response()->json(['error' => "Aucun mois comptable actif trouvé."], 404);
        //     }

        //     try {
        //         DB::beginTransaction();

        //         // --- Étape 1 : trouver la racine de la hiérarchie template ---
        //         $racineTemplate = $categorie;
        //         while ($racineTemplate->parent) {
        //             $racineTemplate = $racineTemplate->parent;
        //         }

        //         // --- Étape 2 : dupliquer toute la hiérarchie pour l'utilisateur ---
        //         $nouvelleRacine = $racineTemplate->dupliquer($user->id, $moisComptable->id, null);

        //         // --- Étape 3 : retrouver la correspondance exacte du nœud cible ---
        //         $cibleCategorie = self::trouverCorrespondanceTemplate($categorie, $racineTemplate, $nouvelleRacine);

        //         DB::commit();
        //     } catch (\Throwable $e) {
        //         DB::rollBack();
        //         Log::error("Erreur duplication template: " . $e->getMessage());
        //         return response()->json(['error' => "Erreur lors de la duplication du template."], 500);
        //     }
        // }
        // --- Cas 2 : catégorie template à dupliquer ---
        else {
            if (!$categorie->is_template) {
                return response()->json(['error' => "Vous n'êtes pas autorisé à utiliser cette catégorie."], 403);
            }

            // Vérifier que la catégorie template est une feuille
            if ($categorie->enfants()->exists()) {
                return response()->json([
                    'error' => "Impossible d'ajouter une opération sur une catégorie template parent."
                ], 422);
            }

            // Récupérer le mois comptable actif
            $moisComptable = $user->moisComptables()->latest()->first();
            if (!$moisComptable) {
                return response()->json(['error' => "Aucun mois comptable actif trouvé."], 404);
            }

            try {
                DB::beginTransaction();

                // --- 1️⃣ Trouver la racine template ---
                $racineTemplate = $categorie;
                while ($racineTemplate->parent) {
                    $racineTemplate = $racineTemplate->parent;
                }

                // --- 2️⃣ Vérifier si la hiérarchie a déjà été copiée pour ce mois ---
                $racineExistante = Categorie::where('user_id', $user->id)
                    ->where('mois_comptable_id', $moisComptable->id)
                    ->where('template_id', $racineTemplate->id)
                    ->first();

                // --- 3️⃣ Si non, dupliquer toute la hiérarchie ---
                if (!$racineExistante) {
                    $racineExistante = $racineTemplate->dupliquer(
                        $user->id,
                        $moisComptable->id,
                        parentId: null,
                        profondeur: null // null = duplique toute la hiérarchie
                    );
                }

                // --- 4️⃣ Trouver maintenant la copie équivalente à la catégorie cible ---
                $cibleCategorie = Categorie::where('user_id', $user->id)
                    ->where('mois_comptable_id', $moisComptable->id)
                    ->where('template_id', $categorie->id)
                    ->first();

                // (Normalement elle existe déjà après duplication, mais on sécurise)
                if (!$cibleCategorie) {
                    return response()->json(['error' => "Erreur lors de la duplication de la hiérarchie."], 500);
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Erreur duplication template: " . $e->getMessage());
                return response()->json(['error' => "Erreur lors de la duplication du template."], 500);
            }
        }


        // --- Création de l’opération ---
        try {
            $operation = DB::transaction(function () use ($validated, $user, $cibleCategorie) {
                return Operation::create([
                    'montant'       => $validated['montant'],
                    'description'   => $validated['description'] ?? null,
                    'date'          => $validated['date'] ?? now(),
                    'nature'        => $validated['nature'],
                    'categorie_id'  => $cibleCategorie->id,
                    'user_id'       => $user->id,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error("Erreur création opération: " . $e->getMessage());
            return response()->json(['error' => "Erreur lors de la création de l'opération."], 500);
        }

        return response()->json([
            'message' => "Opération ajoutée avec succès.",
            'operation' => $operation->load('categorie'),
        ], 201);
    }

    /**
     * 🔁 Trouve la catégorie correspondante après duplication d’un template.
     * Permet de retrouver le bon "feuille" correspondant au modèle original.
     */
    private static function trouverCorrespondanceTemplate($original, $racineTemplate, $nouvelleRacine)
    {
        // On remonte la hiérarchie du template original jusqu’à la racine
        $chemin = collect();
        $courant = $original;
        while ($courant) {
            $chemin->prepend($courant->nom);
            $courant = $courant->parent;
        }

        // On redescend le même chemin sur la nouvelle hiérarchie
        $courant = $nouvelleRacine;
        foreach ($chemin as $nom) {
            $courant = $courant->enfants->firstWhere('nom', $nom) ?? $courant;
        }

        return $courant;
    }





    

    //   // 🔎 Afficher une opération
    // public function show($id)
    // {
    //     $user = Auth::user();
    //     $operation = Operation::findOrFail($id);
    //     if($operation->variable ) {
    //         $variable = $operation->variable;
    //         if($variable->user_id !== $user->id) {
    //             return response()->json("Vous n'est pas Autorisé à acceder à cette donnée", 401);
    //         }
    //     }
    //     if($operation->sousVariable ) {
    //         $sousVariable = $operation->sousVariable;
    //         if($sousVariable->user_id !== $user->id) {
    //             return response()->json("Vous n'est pas Autorisé à acceder à cette donnée", 401);
    //         }
    //     }
    //     return Operation::with(['variable', 'sousVariable'])->findOrFail($id);

    //             // return $operation->with(['variable', 'sousVariable']);
    // }

    

    // public function update(Request $request, $operationId)
    // {
    //     $validated = $request->validate([
    //         'montant' => 'nullable|numeric|min:0',
    //         'description' => 'nullable|string',
    //         'date' => 'nullable|date',
    //     ]);

    //     $operation = Operation::findOrFail($operationId);

    //     try {
    //         DB::transaction(function () use ($operation, $validated) {
    //             $operation->update([
    //                 'montant' => $validated['montant'] ?? $operation->montant,
    //                 'description' => $validated['description'] ?? $operation->description,
    //                 'date' => $validated['date'] ?? $operation->date,
    //             ]);
    //             // L'observer s'occupe du recalcul 
    //         });

    //         return response()->json([
    //             'message' => 'Opération mise à jour avec succès.',
    //             'operation' => $operation->fresh()->load('variable', 'sousVariable'), // Pour renvoyer les données mises à jour
    //         ]);
    //     } catch (\Throwable $e) {
    //         Log::error("Erreur lors de la mise à jour de l'opération : " . $e->getMessage());

    //         return response()->json([
    //             'error' => 'Une erreur est survenue lors de la mise à jour de l\'opération.',
    //         ], 500);
    //     }
    // }
  

    // // 4. destroy($id) — Supprimer une opération

    // public function destroy($operationId)
    // {
    //     $user = Auth::user();
    //     $operation = Operation::findOrFail($operationId);
    //     $variable = $operation->variable ?? $operation->sousVariable->variable;
    //     // dd($variable);
    //     if($variable->user_id !== $user->id) {
    //         return response()->json('Non autorisé', 401);
    //     }
    //     try {
    //         DB::transaction(function () use ($operation) {
                
    //             $operation->delete();
    //         });

    //         return response()->json(['message' => 'Opération supprimée avec succès.']);
    //      } catch (\Throwable $e) {
    //         Log::error("Erreur lors de la suppression de l'opération : " . $e->getMessage());

    //         return response()->json([
    //             'error' => 'Une erreur est survenue lors de la suppression de l\'opération.',
    //         ], 500);
    //     }
    // }

    // 🔎 Afficher une opération
    public function show($id)
    {
        $user = Auth::user();
        $operation = Operation::with('categorie.parent')->findOrFail($id);

        // Vérifie que la catégorie appartient à l'utilisateur
        if ($operation->categorie->user_id !== $user->id) {
            return response()->json("Vous n'êtes pas autorisé à accéder à cette donnée", 401);
        }

        // On reconstruit le format attendu par l'ancien front
        $response = [
            'id'          => $operation->id,
            'montant'     => $operation->montant,
            'nature'      => $operation->nature,
            'description' => $operation->description,
            'date'        => $operation->date,
            'variable'    => null,
            'sousVariable'=> null,
        ];

        // Simulation du format historique
        if ($operation->categorie->niveau === 2) {
            // Catégorie racine (rare pour une opération)
            $response['variable'] = $operation->categorie;
        } elseif (!$operation->categorie->enfants()->exists()) {
            // C’est une sous-variable (feuille)
            $response['sousVariable'] = $operation->categorie;
            $response['variable'] = $operation->categorie->parent;
        } else {
            // C’est une variable simple
            $response['variable'] = $operation->categorie;
        }

        return response()->json($response, 200);
    }

    // ✏️ Mettre à jour une opération
    public function update(Request $request, $operationId)
    {
        $validated = $request->validate([
            'montant' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $user = Auth::user();
        $operation = Operation::with('categorie')->findOrFail($operationId);

        // Sécurité : vérifie que l'opération appartient bien à une catégorie du user
        if ($operation->categorie->user_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        try {
            DB::transaction(function () use ($operation, $validated) {
                $ancienMontant = $operation->montant;

                // Mise à jour de l'opération
                $operation->update([
                    'montant' => $validated['montant'] ?? $operation->montant,
                    'description' => $validated['description'] ?? $operation->description,
                    'date' => $validated['date'] ?? $operation->date,
                ]);

                $difference = ($operation->montant - $ancienMontant);

                // // Ajustement du montant réel dans la catégorie et ses parents
                // if ($difference != 0) {
                //     $categorie = $operation->categorie;
                //     while ($categorie) {
                //         $categorie->increment('depense_reelle', $difference);
                //         $categorie = $categorie->parent;
                //     }
                // }
                // L'observer s'occupe du recalcul 
            });

            // On reconstruit le format attendu par l'ancien front
        $response = [
            'id'          => $operation->id,
            'montant'     => $operation->montant,
            'nature'      => $operation->nature,
            'description' => $operation->description,
            'date'        => $operation->date,
            'variable'    => null,
            'sousVariable'=> null,
        ];

        // Simulation du format historique
        if ($operation->categorie->niveau === 2) {
            // Catégorie racine (rare pour une opération)
            $response['variable'] = $operation->categorie;
        } elseif (!$operation->categorie->enfants()->exists()) {
            // C’est une sous-variable (feuille)
            $response['sousVariable'] = $operation->categorie;
            $response['variable'] = $operation->categorie->parent;
        } else {
            // C’est une variable simple
            $response['variable'] = $operation->categorie;
        }
            return response()->json([
                'message' => 'Opération mise à jour avec succès.',
                'operation' => $response,
            ], 200);
        } catch (\Throwable $e) {
            Log::error("Erreur lors de la mise à jour de l'opération : " . $e->getMessage());

            return response()->json([
                'error' => 'Une erreur est survenue lors de la mise à jour de l\'opération.',
            ], 500);
        }
    }

    // 🗑️ Supprimer une opération
    public function destroy($operationId)
    {
        $user = Auth::user();
        $operation = Operation::with('categorie')->findOrFail($operationId);

        // Vérifie l’accès
        if ($operation->categorie->user_id !== $user->id) {
            return response()->json('Non autorisé', 401);
        }

        Log::info('Operation: ' . $operation . "\nInit try deleting");
        try {
            DB::transaction(function () use ($operation) {
                $montant = $operation->montant;
                $categorie = $operation->categorie;

                // Suppression de l’opération
                $operation->delete();

                // Réduction des montants dans la catégorie et ses parents
                // while ($categorie) {
                //     $categorie->decrement('depense_reelle', $montant);
                //     $categorie = $categorie->parent;
                // }
            });

            return response()->json(['message' => 'Opération supprimée avec succès.'], 200);
        } catch (\Throwable $e) {
            Log::error("Erreur lors de la suppression de l'opération : " . $e->getMessage());

            return response()->json([
                'error' => 'Une erreur est survenue lors de la suppression de l\'opération.',
            ], 500);
        }
    }


    public function lastFiftyOperations()
    {
        $user = Auth::user();

        // Dernier mois comptable de l'utilisateur
        $moisComptable = $user->moisComptables()->latest()->first();
        if (!$moisComptable) {
            return response()->json(['error' => 'Aucun mois comptable trouvé'], 404);
        }

        // Récupérer les opérations liées aux catégories de ce mois
        $operations = Operation::where('user_id', $user->id)
            ->whereHas('categorie', function ($q) use ($moisComptable) {
                $q->where('mois_comptable_id', $moisComptable->id);
            })
            ->latest('date')
            ->take(50)
            ->get();

        return response()->json($operations);
    }

    public function operationsByMonth()
    {
        $user = Auth::user();

        $moisComptables = $user->moisComptables()->with(['categories.operations' => function($q) use ($user) {
            $q->where('user_id', $user->id)->latest('date');
        }])->get();

        return response()->json($moisComptables);
    }

    public function operationsByMonthId($moisId)
    {
        $user = Auth::user();

        $moisComptable = $user->moisComptables()->findOrFail($moisId);

        $operations = Operation::where('user_id', $user->id)
            ->whereHas('categorie', function ($q) use ($moisComptable) {
                $q->where('mois_comptable_id', $moisComptable->id);
            })
            ->latest('date')
            ->get();

        return response()->json($operations);
    }


    
    




}
