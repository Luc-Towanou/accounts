<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\MoisComptable;
use App\Models\Tableau;
use App\Models\Variable;
use App\Services\RegleCalculService;
use App\Services\ReglesCalculService;
use App\Services\VariableService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VariableController extends Controller
{
    //
    protected $service;

    public function __construct(VariableService $service)
    {
        $this->service = $service;
    }
//     public function index()
// {
//     $user = Auth::user();

//     $VariablesSortie = $user
//         ->tableaux()->where('nature', 'sortie')
//         ->with(['variables.sousVariables', 'variables.regleCalcul']) // eager loading
//         ->get()
//         ->pluck('variables')
//         ->flatten();

//     $VariablesEntree = $user
//         ->tableaux()->where('nature', 'entree')
//         ->with(['variables.sousVariables', 'variables.regleCalcul'])
//         ->get()
//         ->pluck('variables')
//         ->flatten();

//     return response()->json([
//         'message' => 'Liste de vos Variables',
//         'sorties' => $VariablesSortie,
//         'entrees' => $VariablesEntree,
//     ], 200);
//     }
//     // 🔍 Liste des variables pour un tableau donné
//     public function indexByTableau($tableauId)
//     {
//         $user = Auth::user();
//         $tableau = Tableau::where('id', $tableauId)
//                           ->where('user_id', $user->id)
//                           ->first();
//         if($tableau) {
//                     return $tableau->variables()->with('sousVariables')->get();

//         }else {
//             return response()->json(['message' => 'Tableau non trouvé ou non autorisé'], 404);
//         }
//     } 
    public function index()
    {
        $user = Auth::user();

        // Récupération des catégories de type 'sortie' et 'entree' selon niveau
        $categoriesSortie = Categorie::where('user_id', $user->id)
            ->where('nature', 'sortie')
            ->with('regleCalcul') // eager loading des règles
            ->get();

        $categoriesEntree = Categorie::where('user_id', $user->id)
            ->where('nature', 'entree')
            ->with('regleCalcul')
            ->get();

        return response()->json([
            'message' => 'Liste de vos catégories',
            'sorties' => $categoriesSortie,
            'entrees' => $categoriesEntree,
        ], 200);
    }

    // 🔍 Liste des catégories pour un niveau donné
    public function indexByNiveau($niveau)
    {
        $user = Auth::user();
        $categories = Categorie::where('user_id', $user->id)
            ->where('niveau', $niveau)
            ->with('regleCalcul')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json(['message' => 'Aucune catégorie trouvée pour ce niveau'], 404);
        }

        return response()->json($categories, 200);
    }

    // Créer une catégorie
    // public function store(Request $request, ReglesCalculService $validator)
    // {
    //     $validated = $request->validate([
    //         'tableau_id' => 'required|exists:tableaus,id',
    //         'nom' => 'required|string',
    //         'nature' => 'required|in:entree,sortie',
    //         'niveau' => 'required|integer|min:1',
    //         'budget_prevu' => 'nullable|numeric',
    //         'calcule' => 'nullable|boolean',
    //         'regle.expression' => 'nullable|string',

    //         // Si type = sous-tableau
    //         'sous_variables' => 'nullable|array',
    //         'sous_variables.*.nom' => 'required|string',
    //         'sous_variables.*.budget_prevu' => 'nullable|numeric',
    //         'sous_variables.*.calcule' => 'boolean',
    //     ]);

    //     $user = Auth::user();

    //     // Vérifie unicité du nom pour ce niveau
    //     if (Categorie::where('user_id', $user->id)
    //         ->where('nom', $validated['nom'])
    //         ->where('niveau', $validated['niveau'])
    //         ->exists()) {
    //         return response()->json([
    //             'message' => 'Une catégorie avec ce nom existe déjà pour ce niveau',
    //         ], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // Validation de la règle avant création
    //         if (($validated['calcule'] ?? false) && isset($validated['regle']['expression'])) {
    //             $validator->validerExpression($validated['regle']['expression']);
    //         }

    //         // Création de la catégorie
    //         $categorie = Categorie::create([
    //             'user_id'       => $user->id,
    //             'nom'           => $validated['nom'],
    //             'nature'        => $validated['nature'],
    //             'niveau'        => $validated['niveau'],
    //             'budget_prevu'  => $validated['budget_prevu'] ?? null,
    //             'calcule'       => $validated['calcule'] ?? false,
    //         ]);

    //         // Création de la règle de calcul si nécessaire
    //         if ($categorie->calcule && isset($validated['regle']['expression'])) {
    //             $categorie->regleCalcul()->create([
    //                 'user_id'   => $user->id,
    //                 'expression'=> $validated['regle']['expression'],
    //             ]);
    //         }

    //         DB::commit();

    //         return $categorie->load('regleCalcul');

    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Erreur lors de la création de la catégorie',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }



    // // Créer une variable dans un tableau

    // public function store(Request $request, ReglesCalculService $validator)
    // {
    //     $validated = $request->validate([
    //         'tableau_id' => 'required|exists:tableaus,id',
    //         'nom' => 'required|string',
    //         'type' => 'required|in:simple,sous-tableau',
    //         'budget_prevu' => 'nullable|numeric',
    //         'calcule' => 'nullable|boolean',
    //         // 'calcule' => 'sometimes|boolean',
    //         'regle.expression' => 'nullable|string',

    //         // Si type = sous-tableau
    //         'sous_variables' => 'nullable|array',
    //         'sous_variables.*.nom' => 'required|string',
    //         'sous_variables.*.budget_prevu' => 'nullable|numeric',
    //         'sous_variables.*.calcule' => 'boolean',
    //     ]);

    //     $user = Auth::user(); 
    //     $tableau = Tableau::findOrFail($validated['tableau_id']);

    //     if ($tableau->user_id !== $user->id) {
    //         abort(401, 'Non autorisé');
    //     }

    //     if (Variable::where('tableau_id', $tableau->id)
    //                 ->where('nom', $validated['nom'])
    //                 ->exists()) {
    //         return response()->json([
    //             'message' => 'Une variable avec ce nom existe déjà pour ce tableau',
    //         ], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // 🔹 Validation AVANT la création
    //         if ($validated['type'] === 'sous-tableau') {
    //             foreach ($validated['sous_variables'] ?? [] as $svData) {
    //                 if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
    //                     $validator->validerExpression($svData['regle']['expression']);
    //                 }
    //             }
    //         } elseif (($validated['calcule'] ?? false) && isset($validated['regle']['expression'])) {
    //             $validator->validerExpression($validated['regle']['expression']);
    //         }

    //         // 🔹 Création de la variable
    //         $variable = Variable::create([
    //             'user_id'       => $user->id,
    //             'tableau_id'    => $validated['tableau_id'],
    //             'nom'           => $validated['nom'],
    //             'type'          => $validated['type'],
    //             'budget_prevu'  => $validated['budget_prevu'] ?? null,
    //             'calcule'       => $validated['calcule'] ?? false //$request->calcule ?? false,
    //         ]);

    //         // 🔹 Gestion des sous-variables
    //         if ($variable->type === 'sous-tableau') {
    //             foreach ($validated['sous_variables'] ?? [] as $svData) {
    //                 $variable->sousVariables()->create([
    //                     'user_id'       => $user->id,
    //                     'nom'           => $svData['nom'],
    //                     'budget_prevu'  => $svData['budget_prevu'] ?? null,
    //                     'calcule'       => false, 
    //                 ]);
    //             }
    //         } 
    //         // 🔹 Gestion de la règle de calcul
    //         elseif ($variable->calcule) {
    //             $variable->regleCalcul()->create([
    //                 'user_id'   => $user->id,
    //                 'expression'=> $validated['regle']['expression'],
    //             ]);
    //         }

    //         DB::commit();

    //         return $variable->load('regleCalcul', 'sousVariables.regleCalcul');

    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Erreur lors de la création de la variable',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function store(Request $request, ReglesCalculService $validator)
    {
        $validated = $request->validate([
            'tableau_id' => 'required|exists:categories,id', // le parent (tableau niveau 1)
            'nom' => 'required|string',
            'type' => 'required|in:simple,sous-tableau',
            'budget_prevu' => 'nullable|numeric',
            'calcule' => 'nullable|boolean',
            'regle.expression' => 'nullable|string',

            // Si type = sous-tableau
            'sous_variables' => 'nullable|array',
            'sous_variables.*.nom' => 'required|string',
            'sous_variables.*.budget_prevu' => 'nullable|numeric',
            'sous_variables.*.calcule' => 'boolean',
            // 'sous_variables.*.regle.expression' => 'nullable|string',
        ]);

        $user = Auth::user(); 
        $parentCategorie = Categorie::findOrFail($validated['tableau_id']);

        // Vérification que le parent est bien un tableau (niveau 1)
        if ($parentCategorie->niveau !== 1 || $parentCategorie->user_id !== $user->id) {
            abort(401, 'Non autorisé ou parent invalide');
        }

        // Vérifier si une variable du même nom existe déjà dans ce tableau
        if (Categorie::where('parent_id', $parentCategorie->id)
                    ->where('nom', $validated['nom'])
                    ->where('niveau', 2)
                    ->exists()) {
            return response()->json([
                'message' => 'Une variable avec ce nom existe déjà pour ce tableau',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 🔹 Validation AVANT la création
            if ($validated['type'] === 'sous-tableau') {
                // foreach ($validated['sous_variables'] ?? [] as $svData) {
                //     if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
                //         $validator->validerExpression($svData['regle']['expression']);
                //     }
                // }
            } elseif (($validated['calcule'] ?? false) && isset($validated['regle']['expression'])) {
                $validator->validerExpression($validated['regle']['expression']);
            }

            // 🔹 Création de la variable (niveau 2)
            $variable = Categorie::create([
                'user_id'       => $user->id,
                'mois_comptable_id' => $parentCategorie->mois_comptable_id,
                'parent_id'     => $parentCategorie->id,
                'nom'           => $validated['nom'],
                'budget_prevu'  => $validated['budget_prevu'] ?? null,
                'calcule'       => $validated['calcule'] ?? false,
                'niveau'        => 2,
                'nature'        => $parentCategorie->nature, // hérite de la nature du parent
            ]);

            // 🔹 Gestion des sous-variables (niveau 3)
            // if ($variable->type === 'sous-tableau') {
                foreach ($validated['sous_variables'] ?? [] as $svData) {
                    $sousVar = Categorie::create([
                        'user_id'       => $user->id,
                        'mois_comptable_id' => $parentCategorie->mois_comptable_id,
                        'parent_id'     => $variable->id,
                        'nom'           => $svData['nom'],
                        'budget_prevu'  => $svData['budget_prevu'] ?? null,
                        'calcule'       => false,
                        'niveau'        => 3,
                        'nature'        => $parentCategorie->nature,
                    ]);

                    if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
                        $sousVar->regleCalcul()->create([
                            'user_id'   => $user->id,
                            'expression'=> $svData['regle']['expression'],
                        ]);
                    }
                }
            // } 
            // 🔹 Gestion de la règle de calcul
            if ($variable->calcule) {
                $variable->regleCalcul()->create([
                    'user_id'   => $user->id,
                    'expression'=> $validated['regle']['expression'],
                ]);
            }

            DB::commit();

            return $variable;    //  $variable->load('regleCalcul', 'enfants.regleCalcul');

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création de la variable',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    // public function store(Request $request, ReglesCalculService $validator)
    // {
    //     $validated = $request->validate([
    //         'tableau_id' => 'required|exists:tableaus,id',
    //         'nom' => 'required|string',
    //         'type' => 'required|in:simple,sous-tableau',
    //         'budget_prevu' => 'nullable|numeric',               
    //         'calcule' => 'boolean',            
    //         'regle.expression' => 'nullable|string',

    //         // Si type = sous-tableau
    //         'sous_variables' => 'nullable|array',
    //         'sous_variables.*.nom' => 'required|string',
    //         'sous_variables.*.budget_prevu' => 'nullable|numeric',
    //         'sous_variables.*.calcule' => 'boolean',            
    //         // 'sous_variables.*.regle.expression' => 'nullable|string',
    //     ]);
        
    //     //connected user

    //     $user = Auth::user(); 

    //     $tableau = Tableau::findOrFail($validated['tableau_id']);

    //     // dd($tableau) ;
        
    //     if ($tableau->user_id !== $user->id) {
    //         abort(401, 'Non autorisé');
    //     }   
        
    //     if (Variable::where('tableau_id', $tableau->id)
    //                 ->where('nom', $validated['nom'])
    //                 ->exists()) {
    //         return response()->json([
    //             'message' => 'Une variable avec ce nom existe déjà pour ce tableau',
    //         ], 422);
    //     }
        
    //     $variable = DB::transaction(function () use ($validated, $validator, $user) {

    //         // 🔹 Validation AVANT la création
    //         if ($validated['type'] === 'sous-tableau') {
    //             foreach ($validated['sous_variables'] ?? [] as $svData) {
    //                 if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
    //                     $validator->validerExpression($svData['regle']['expression']);
    //                 }
    //             }
    //         } elseif ($validated['calcule']) {
    //             $validator->validerExpression($validated['regle']['expression']);
    //         }

    //         // 🔹 Création seulement après validation
    //         $variable = Variable::create([
    //             'user_id'       => $user->id,
    //             'tableau_id'    => $validated['tableau_id'],
    //             'nom'           => $validated['nom'],
    //             'type'          => $validated['type'],
    //             'budget_prevu'  => $validated['budget_prevu'] ?? null,
    //             'calcule'       => $validated['calcule'] ?? false,
    //         ]);

    //         if ($variable->type === 'sous-tableau') {
    //             foreach ($validated['sous_variables'] ?? [] as $svData) {
    //                 $sousvariable = $variable->sousVariables()->create([
    //                     'user_id'       => $user->id,
    //                     'nom'           => $svData['nom'],
    //                     'budget_prevu'  => $svData['budget_prevu'] ?? null,
    //                     'calcule'       => false,
    //                     // 'calcule'       => $svData['calcule'] ?? false,
    //                 ]);

    //             }
    //         } elseif ($variable->calcule) {
    //             $variable->regleCalcul()->create([
    //                 'user_id'   => $user->id,
    //                 'expression'=> $validated['regle']['expression'],
    //             ]);
    //         }

    //         return $variable;
    //     });

    // return $variable->load('regleCalcul', 'sousVariables.regleCalcul');
    // }



    public function show($id)
    {

        $user = Auth::user() ; 

        $variable = Variable::findOrFail($id);

        $tableau = Tableau::findOrFail($variable->tableau_id);

    
        
        if ($tableau->user_id !== $user->id) {
            abort(401, 'Non autorisé');
        }   
        
        
        // $mois = MoisComptable::where('user_id', $user->id)
        //                      ->where('id', $tableau->mois_comptable_id)
        //                      ->first();

        
        return Variable::with('sousVariables', 'operations', 'regleCalcul')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tableau_id' => 'nullable|exists:tableaux,id',
            'nom' => 'nullable|string',
            'type' => 'nullable|in:simple,sous-tableau',
            'budget_prevu' => 'nullable|numeric',               
            ]);
        $variable = Variable::findOrFail($id);
        $user = Auth::user() ; 


        $tableau = Tableau::findOrFail($variable->tableau_id);

        
        // $mois = MoisComptable::where('user_id', $user->id)
        //                      ->where('id', $tableau->mois_comptable_id)
        //                      ->first();

        if ($tableau->user_id !== $user->id) {
            abort(401, 'Non autorisé');
        }   
        
        if ($request->has('tableau_id')) $variable->tableau_id = $request->tableau_id;
        if ($request->has('nom')) $variable->nom = $request->nom;
        if ($request->has('type')) $variable->type = $request->type;
        if ($request->has('budget_prevu')) $variable->budget_prevu = $request->budget_prevu;
        $variable->save();

        // $variable->update($request->only(['nom', 'budget_prevu', 'depense_reelle', 'tableau_id' , 'type'  ]));
        return $variable;
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $variable = Variable::findOrFail($id);
            $user = Auth::user(); 

            $tableau = Tableau::findOrFail($variable->tableau_id);

            // Vérification autorisation
            if ($tableau->user_id !== $user->id) {
                DB::rollBack();
                return response()->json(['message' => 'Non autorisé'], 401);
            }

            // Vérification sous-variables
            if ($variable->sousVariables()->exists()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Impossible de supprimer cette variable car elle contient des sous-variables. Supprimez-les d\'abord.'
                ], 400);
            }

            // Vérification règle de calcul
            $regleCalcul = new RegleCalculService();
            $parente = $regleCalcul->variableRegleCalcul($variable);
            if ($parente) {
                DB::rollBack();
                return response()->json([
                    'message' => "Cette variable est déjà utilisée dans la règle de calcul de : " . $parente->nom
                ], 400);
            }

            // Suppression
            $variable->delete();

            DB::commit();

            return response()->json(['message' => 'Variable supprimée avec succès'], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la suppression de la variable',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    // public function destroy($id)
    // {
    //     $variable  = Variable::findOrFail($id);
    //     // $this->authorize('delete', $variable);

    //     $user = Auth::user() ; 

    //     $tableau = Tableau::findOrFail($variable->tableau_id);

    //     if ($tableau->user_id !== $user->id) {
    //         abort(401, 'Non autorisé');
    //     }   
        
        
        
    //     $regleCalcul = new RegleCalculService();
    //     $parente = $regleCalcul->variableRegleCalcul($variable);
    //     if ($parente) { 
    //         throw new Exception("Cette variable est déjà utilisée dans la règle de calcul de : " . $parente->nom);
    //     }
    //     Variable::destroy($id);
    //     return response()->json(['message' => 'Variable supprimée avec succès']); 
    // }

     /**
     * Retourne le montant d’une variable pour une période donnée
     */
    public function montant(Request $request, $id)
    {
        $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin'   => 'nullable|date|after_or_equal:date_debut',
        ]);

        $user = Auth::user(); 

        $variable = Variable::findOrFail($id);

        if ($variable->user_id !== $user->id) {
            abort(401, 'Non autorisé');
        }  

        $tableau = $variable->tableau;
        $mois = $tableau->moisComptable;
        
        // Si l’utilisateur a fourni une date_debut, on la parse
        if ($request->filled('date_debut')) {
            $dateDebut = Carbon::parse($request->input('date_debut'));
        } else {
            // Eloquent cast sur date_debut renvoie déjà un Carbon
            $dateDebut = $mois->date_debut;
        }

        // Même logique pour date_fin
        if ($request->filled('date_fin')) {
            $dateFin = Carbon::parse($request->input('date_fin'));
        } else {
            $dateFin = $mois->date_fin;
        }              
        // dd($dateDebut, $dateFin);  
        
        $montant = $this->service->calculerMontant($variable, $dateDebut, $dateFin);

        return response()->json([
            'variable_id' => $variable->id,
            'nom'         => $variable->nom ?? null,
            'date_debut'  => $dateDebut->toDateString(),
            'date_fin'    => $dateFin->toDateString(), 
            'montant'     => $montant, 
        ]); 
    } 
} 
