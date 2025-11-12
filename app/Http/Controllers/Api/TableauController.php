<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategorieResource;
use App\Models\Categorie;
use App\Models\MoisComptable;
use App\Models\Tableau;
use App\Models\User;
use App\Services\ReglesCalculService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TableauController extends Controller
{
    //

    // 
    // public function index()
    // {
    //     $user = Auth::user();
    //     // $tableauSortie = $user->tableaux()
    //     //                                   ->where('nature', 'sortie')
    //     //                                   ->with('variables', 'variables.sousVariables', 'variables.regleCalcul')->get();
    //     // $tableauEntree = $user->tableaux()
    //     //                                   ->where('nature', 'entree')
    //     //                                   ->with('variables', 'variables.sousVariables', 'variables.regleCalcul')->get();
    //     $tableau = $user->tableaux()->with('variables', 'variables.sousVariables', 'variables.regleCalcul')->get();
    //     return response()->json([
    //     'message' => 'Liste de vos tableaux',
    //     // 'sorties' => $tableauSortie,
    //     // 'entrees' => $tableauEntree,
    //     'tableaux' => $tableau,
    //     ],200);
    // }
    // 🔹 Lister tous les "tableaux" → maintenant ce sont les catégories de niveau 1
    public function index()
    {
        $user = Auth::user();

        $categories = Categorie::where('user_id', $user->id)
            ->whereNull('parent_id') // Niveau 1
            ->with('enfants') // Charger les sous-catégories
            ->get();

        return response()->json([
            'message' => 'Liste de vos tableaux',
            'tableaux' => $categories,
        ], 200);
    }

     // création avec variables et sous-variables intégrées
    // public function store(Request $request, ReglesCalculService $validator)
    // {
    //     $data = $request->validate([
    //         'mois_comptable_id' => 'required|exists:mois_comptables,id',
    //         'nom' => 'required|string',
    //         'budget_prevu' => 'nullable|numeric',
    //         'nature' => 'in:entree,sortie',

    //         'variables' => 'nullable|array',
    //         'variables.*.nom' => 'required|string',
    //         'variables.*.type' => 'required|in:simple,sous-tableau',
    //         'variables.*.budget_prevu' => 'nullable|numeric',
    //         'variables.*.calcule' => 'boolean',            
    //         'variables.*.regle.expression' => 'nullable|string',

    //         // 'variables.*.sous_variables' => 'required_if:variables.*.type,sous-tableau|array',
    //         'variables.*.sous_variables' => 'nullable|array',
    //         'variables.*.sous_variables.*.nom' => 'required|string',
    //         'variables.*.sous_variables.*.budget_prevu' => 'nullable|numeric',
    //         'variables.*.sous_variables.*.calcule' => 'boolean',            
    //         'variables.*.sous_variables.*.regle.expression' => 'nullable|string',
    //     ]);

    //     //connected user
    //     $user = Auth::user() ; 
    //     //verification appartenance du mois

    //     // if (!$request->user()) {
    //     // abort(403, 'Non authentifié');
    //     // }

    //         $mois = MoisComptable::whereKey($data['mois_comptable_id'])
    //                              ->where('user_id', $user->id)
    //                              ->firstOrFail();
    //     if (Categorie::where('mois_comptable_id', $mois->id)
    //             ->where('nom', $data['nom'])
    //             ->where('niveau', 1)
    //             ->exists()) {
    //         return response()->json([
    //             'message' => 'Une grande-categorie portant ce nom existe déjà pour ce mois comptable',
    //         ], 422);
    //     }
    //     try {    
    //         $tableau = DB::transaction(function () use ($data, $validator, $user) {
                
    //             // 1. Validation AVANT la création  
    //             foreach ($data['variables'] ?? [] as $vData) {
    //                 if (($vData['calcule'] ?? false) && isset($vData['regle']['expression'])) {
    //                     $validator->validerExpression($vData['regle']['expression']);
    //                 }
    //                 if ($vData['type'] === 'sous-tableau') {
    //                     foreach ($vData['sous_variables'] ?? [] as $svData) {
    //                         if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
    //                             $validator->validerExpression($svData['regle']['expression']);
    //                         }
    //                     }
    //                 }
    //             }

    //             // 2. Création Tableau + Variables + SousVariables  
    //             $tableau = Tableau::create([
    //                 'user_id'           => $user->id,
    //                 'mois_comptable_id' => $data['mois_comptable_id'],
    //                 'nom'               => $data['nom'],
    //                 'budget_prevu'      => $data['budget_prevu'] ?? null,
    //                 'nature'            => $data['nature'],
    //             ]);

    //             foreach ($data['variables'] ?? [] as $vData) {
    //                 if ($vData['type'] === 'sous-tableau') {
    //                     $variable = $tableau->variables()->create([
    //                         'user_id'       => $user->id,
    //                         'nom'           => $vData['nom'],
    //                         'budget_prevu'  => $vData['budget_prevu'] ?? null,
    //                         'type'          => 'sous-tableau',
    //                     ]);

    //                     foreach ($vData['sous_variables'] ?? [] as $svData) {
    //                         $sousVar = $variable->sousVariables()->create([
    //                             'user_id'       => $user->id,
    //                             'nom'           => $svData['nom'],
    //                             'calcule'       => $svData['calcule'] ?? false,
    //                             'budget_prevu'  => $svData['budget_prevu'] ?? null,
    //                         ]);

    //                         if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
    //                             $sousVar->regleCalcul()->create([
    //                                 'user_id'   => $user->id,
    //                                 'expression'=> $svData['regle']['expression'],
    //                             ]);
    //                         }
    //                     }
    //                 } else {
    //                     $variable = $tableau->variables()->create([
    //                         'user_id'       => $user->id,
    //                         'nom'           => $vData['nom'],
    //                         'type'          => 'simple',
    //                         'calcule'       => $vData['calcule'] ?? false,
    //                         'budget_prevu'  => $vData['budget_prevu'] ?? null,
    //                     ]);

    //                     if (($vData['calcule'] ?? false) && isset($vData['regle']['expression'])) {
    //                         $variable->regleCalcul()->create([
    //                             'user_id'   => $user->id,
    //                             'expression'=> $vData['regle']['expression'],
    //                         ]);
    //                     }
    //                 }
    //             }
    //             return $tableau;
    //         });
    //         return $tableau->load('variables', 'variables.sousVariables', 'variables.regleCalcul');
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'message' => 'Impossible de créer le mois',
    //             'error'   => $e->getMessage()
    //         ], 422);
    //     }

    //  try {    
    //     $tableau = DB::transaction(function () use ($data, $validator, $user) {
    //             $tableau = Tableau::create([
    //             'user_id'           => $user->id,
    //             'mois_comptable_id' => $data['mois_comptable_id'],
    //             'nom'               => $data['nom'],
    //             'budget_prevu'      => $data['budget_prevu'] ?? null,
    //             'nature'            => $data['nature'],
    //         ]);

    //         foreach ($data['variables'] ?? [] as $vData) {
    //             // === Sous-tableau ===
    //             if ($vData['type'] === 'sous-tableau') {
    //                 $variable = $tableau->variables()->create([
    //                     'user_id' => $user->id,
    //                     'nom' => $vData['nom'],
    //                     'budget_prevu' => $vData['budget_prevu'] ?? null,
    //                     'type' => 'sous-tableau',
    //                 ]);

    //                 foreach ($vData['sous_variables'] ?? [] as $svData) {
    //                     $sousVar = $variable->sousVariables()->create([
    //                         'user_id' => $user->id,
    //                         'nom' => $svData['nom'],
    //                         // 'type' => 'simple',
    //                         'calcule' => $svData['calcule'] ?? false,
    //                         'budget_prevu' => $svData['budget_prevu'] ?? null,
    //                     ]);

    //                     if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
    //                         $validator->validerExpression($svData['regle']['expression']);
    //                         $sousVar->regleCalcul()->create([
    //                             'user_id'       => $user->id,
    //                             'expression' => $svData['regle']['expression'],
    //                         ]);
    //                     }
    //                 }

    //             } else {
    //                 // === Variable simple ou résultat ===
    //                 $variable = $tableau->variables()->create([
    //                     'user_id' => $user->id,
    //                     'nom' => $vData['nom'],
    //                     'type' => 'simple',
    //                     'calcule' => $vData['calcule'] ?? false,
    //                     'budget_prevu' => $vData['budget_prevu'] ?? null,
    //                 ]);

    //                 if (($vData['calcule'] ?? false) && isset($vData['regle']['expression'])) {
                        
    //                     $validator->validerExpression($vData['regle']['expression']);
    //                     $variable->regleCalcul()->create([
    //                         'user_id'       => $user->id,
    //                         'expression' => $vData['regle']['expression'],
    //                     ]);
    //                 }
    //             }
    //         }
    //         return $tableau;
    //     });
    //     return $tableau->load('variables', 'variables.sousVariables', 'variables.regleCalcul');
    //     } catch (\Throwable $e) {
    //     return response()->json([
    //         'message' => 'Impossible de créer le mois',
    //         'error'   => $e->getMessage()
    //     ], 422);
    // }
    // }
    public function store(Request $request, ReglesCalculService $validator)
    {
        $data = $request->validate([
            'mois_comptable_id' => 'required|exists:mois_comptables,id',
            'nom' => 'required|string',
            'budget_prevu' => 'nullable|numeric',
            'nature' => 'in:entree,sortie',

            'variables' => 'nullable|array',
            'variables.*.nom' => 'required|string',
            'variables.*.type' => 'required|in:simple,sous-tableau',
            'variables.*.budget_prevu' => 'nullable|numeric',
            'variables.*.calcule' => 'boolean',            
            'variables.*.regle.expression' => 'nullable|string',

            'variables.*.sous_variables' => 'nullable|array',
            'variables.*.sous_variables.*.nom' => 'required|string',
            'variables.*.sous_variables.*.budget_prevu' => 'nullable|numeric',
            // 'variables.*.sous_variables.*.calcule' => 'boolean',            
            'variables.*.sous_variables.*.regle.expression' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Vérification d’appartenance du mois à l’utilisateur
        $mois = MoisComptable::whereKey($data['mois_comptable_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Vérifier si la grande catégorie existe déjà
        if (Categorie::where('mois_comptable_id', $mois->id)
            ->where('nom', $data['nom'])
            ->where('niveau', 1)
            ->exists()) {
            return response()->json([
                'message' => 'Une grande-catégorie portant ce nom existe déjà pour ce mois comptable',
            ], 422);
        }

        try {
            $categorie = DB::transaction(function () use ($data, $validator, $user) {

                // 1️⃣ Validation des règles de calcul avant toute insertion
                foreach ($data['variables'] ?? [] as $vData) {
                    if (($vData['calcule'] ?? false) && isset($vData['regle']['expression'])) {
                        $validator->validerExpression($vData['regle']['expression']);
                    }

                    // if ($vData['type'] === 'sous-tableau') { 
                        // foreach ($vData['sous_variables'] ?? [] as $svData) {
                        //     if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
                        //         $validator->validerExpression($svData['regle']['expression']);
                        //     }
                        // }
                    // } 
                }

                // 2️⃣ Création de la catégorie principale (niveau 1)
                $categorie = Categorie::create([
                    'user_id' => $user->id,
                    'mois_comptable_id' => $data['mois_comptable_id'],
                    'nom' => $data['nom'],
                    'budget_prevu' => $data['budget_prevu'] ?? null,
                    'nature' => $data['nature'],
                    'niveau' => 1,
                ]);

                // 3️⃣ Création des sous-catégories (ex-variables)
                foreach ($data['variables'] ?? [] as $vData) {
                    $variable = Categorie::create([
                        'user_id' => $user->id,
                        'mois_comptable_id' => $data['mois_comptable_id'],
                        'parent_id' => $categorie->id,
                        'nom' => $vData['nom'],
                        'budget_prevu' => $vData['budget_prevu'] ?? null,
                        'calcule' => $vData['calcule'] ?? false,
                        'niveau' => 2,
                        'nature' => $data['nature'], // hérite de la nature du parent
                    ]);

                    if (($vData['calcule'] ?? false) && isset($vData['regle']['expression'])) {
                        $variable->regleCalcul()->create([
                            'user_id' => $user->id,
                            'expression' => $vData['regle']['expression'],
                        ]);
                    }

                    // 4️⃣ Sous-variables → niveau 3
                    if ($vData['type'] === 'sous-tableau') {
                        foreach ($vData['sous_variables'] ?? [] as $svData) {
                            $sousVar = Categorie::create([
                                'user_id' => $user->id,
                                'mois_comptable_id' => $data['mois_comptable_id'],
                                'parent_id' => $variable->id,
                                'nom' => $svData['nom'],
                                'budget_prevu' => $svData['budget_prevu'] ?? null,
                                // 'calcule' => $svData['calcule'] ?? false,
                                'calcule' => false,
                                'niveau' => 3,
                                'nature' => $data['nature'],
                            ]);

                            // if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
                            //     $sousVar->regleCalcul()->create([
                            //         'user_id' => $user->id,
                            //         'expression' => $svData['regle']['expression'],
                            //     ]);
                            // }
                        }
                    }
                }

                return $categorie;
            });

            // Charger les enfants pour le retour JSON
            // $categorie->load(['children.children', 'regleCalcul']);

            return response()->json([
                'message' => 'Catégorie principale créée avec succès 🎉',
                'data' => new CategorieResource($categorie)
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Impossible de créer la catégorie principale',
                'error' => $e->getMessage()
            ], 422);
        }
    }


    // public function show($id)
    // {
    //     $user = Auth::user();
    //     $tableau = Tableau::findOrFail($id);
    //     if($tableau->user_id !== $user->id){
    //         return response()->json("Non auturisé", 401);
    //     }
    //     return Tableau::with('variables', 'variables.sousVariables', 'variables.regleCalcul')->findOrFail($id);
    // }

    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'nom' => 'sometimes|string',
    //         'budget_prevu' => 'nullable|numeric',
    //         'nature' => 'nullable|in:entree,sortie',
    //     ]);
    //     $user = Auth::user();
    //     $tableau = Tableau::findOrFail($id);
    //     if($tableau->user_id !== $user->id){
    //         return response()->json("Non auturisé", 401);
    //     }
    //     // $tableau->update($data);
    //     if ($request->has('nom')) $tableau->nom = $request->nom;
    //     if ($request->has('budget_prevu')) $tableau->budget_prevu = $request->budget_prevu;
    //     if ($request->has('nature')) $tableau->nature = $request->nature;
    //     $tableau->save();

    //      return response()->json([
    //         'message' => 'Tableau mis à jour',
    //         'tableau' => $tableau,
    //     ]);
    // }

    // public function destroy($id)
    //     {
    //     $user = Auth::user();

    //     try {
    //         DB::beginTransaction();

    //         $tableau = Tableau::findOrFail($id);

    //         // Vérification de l'autorisation
    //         if ($tableau->user_id !== $user->id) {
    //             return response()->json(["message" => "Non autorisé"], 401);
    //         }

    //         // Vérification des variables liées
    //         if ($tableau->variables()->exists()) {
    //             return response()->json([
    //                 "message" => "Impossible de supprimer ce tableau car il contient des variables. Supprimez-les d'abord."
    //             ], 400);
    //         }

    //         // Suppression
    //         $tableau->delete();

    //         DB::commit();

    //         return response()->json([
    //             "message" => "Tableau supprimé avec succès"
    //         ], 200);

    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             "message" => "Erreur lors de la suppression du tableau",
    //             "error"   => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // 🔹 Afficher un "tableau" (catégorie niveau 1)
    public function show($id)
    {
        $user = Auth::user();
        $categorie = Categorie::with('enfants')->findOrFail($id);

        if ($categorie->user_id !== $user->id) {
            return response()->json("Non autorisé", 401);
        }

        return response()->json($categorie, 200);
    }

    // 🔹 Mettre à jour un "tableau" (catégorie niveau 1)
    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => 'sometimes|string',
            'budget_prevu' => 'nullable|numeric',
            'nature' => 'nullable|in:entree,sortie',
        ]);

        $user = Auth::user();
        $categorie = Categorie::findOrFail($id);

        if ($categorie->user_id !== $user->id) {
            return response()->json("Non autorisé", 401);
        }

        if ($request->has('nom')) $categorie->nom = $request->nom;
        if ($request->has('budget_prevu')) $categorie->budget_prevu = $request->budget_prevu;
        if ($request->has('nature')) $categorie->nature = $request->nature;

        $categorie->save();

        return response()->json([
            'message' => 'Tableau mis à jour',
            'tableau' => $categorie,
        ], 200);
    }

    // 🔹 Supprimer un "tableau" (catégorie niveau 1)
    public function destroy($id)
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $categorie = Categorie::findOrFail($id);

            if ($categorie->user_id !== $user->id) {
                return response()->json(["message" => "Non autorisé"], 401);
            }

            // Vérification qu'il n'y a pas de sous-catégories
            if ($categorie->enfants()->exists()) {
                return response()->json([
                    "message" => "Impossible de supprimer ce tableau car il contient des sous-catégories. Supprimez-les d'abord."
                ], 400);
            }

            // Vérification des opérations liées
            if ($categorie->operations()->exists()) {
                return response()->json([
                    "message" => "Impossible de supprimer ce tableau car il contient des opérations. Supprimez-les d'abord."
                ], 400);
            }

            $categorie->delete();
            DB::commit();

            return response()->json([
                "message" => "Tableau supprimé avec succès"
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Erreur lors de la suppression du tableau",
                "error"   => $e->getMessage()
            ], 500);
        }
    }
    // public function destroy($id)
    // {
    //     $user = Auth::user();
    //     $tableau = Tableau::findOrFail($id);
    //     if($tableau->user_id !== $user->id){
    //         return response()->json("Non auturisé", 401);
    //     }
    //     Tableau::destroy($id);
    //     return response()->json(['message' => 'Tableau supprimé avec succès'], 200);
    // }

    // public function moisActifTableaux() {
    //     $user = Auth::user(); 
    //     $moisActif = MoisComptable::where('user_id', $user->id)
    //                             ->where('annee', now()->year)
    //                             ->where('mois', now()->month)
    //                             ->get();
    // }
    public function moisTableaux($moisId) {
        $user = Auth::user(); 
        $mois_comptable = MoisComptable::where('user_id', $user->id)
                                ->where('id', $moisId)
                                ->first();
        if(!$mois_comptable) {
            return response()->json('Mois comptable inexistant pour cet utilisateur', 401);
        }
        $tableaux = $mois_comptable->tableaux()
                                   ->with('variables', 'variables.sousVariables', 'variables.regleCalcul')
                                   ->get();
        return response()->json([
        'message' => 'Liste des tableaux de ' . $mois_comptable->mois . ' ' . $mois_comptable->annee, 
        'tableaux' => $tableaux,
        ],200);
    }

}
