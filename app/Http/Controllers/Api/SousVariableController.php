<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\RegleCalcul;
use App\Models\SousVariable;
use App\Models\Tableau;
use App\Models\Variable;
use App\Services\RegleCalculService;
use App\Services\ReglesCalculService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SousVariableController extends Controller
{
    //
     // 🔍 Lister toutes les sous-variables
   public function index()
{
    $user = Auth::user();

    $VariablesSortie = $user->tableaux()->where('nature', 'sortie')
        ->with(['variables.sousVariables']) // eager loading
        ->get()
        ->pluck('variables')
        ->flatten()
        ->pluck('sousVariables')
        ->flatten();

    $VariablesEntree = $user->tableaux()->where('nature', 'entree')
        ->with(['variables.sousVariables'])
        ->get()
        ->pluck('variables')
        ->flatten()
        ->pluck('sousVariables')
        ->flatten();

    return response()->json([
        'message' => 'Liste de vos Sous-Variables',
        'sorties' => $VariablesSortie,
        'entrees' => $VariablesEntree,
    ], 200);
    }

    // 🔍 Lister les sous-variables d’une variable donnée
    public function indexByVariable($variableId)
    {
        $user = Auth::user();
        $variable = Variable::findOrFail($variableId);
        
        if ($variable->user_id !== $user->id) {
            abort(401, 'Non autorisé');
        } 
        return $variable->sousVariables;
    }

    // 🔍 Liste des sous-variables pour un tableau donné
    public function indexByTableau($tableauId)
    {
        $user = Auth::user();
        $tableau = Tableau::where('id', $tableauId)
                          ->where('user_id', $user->id)
                          ->first();
        
        if($tableau) {
                    return $tableau->variables()->with('sousVariables')->get();

        }else {
            return response()->json(['message' => 'Tableau non trouvé ou non autorisé'], 404);
        }
    }

    // ➕ Créer une sous-variable
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'variable_id' => 'nullable|exists:variables,id',
    //         'nom' => 'required|string',
    //         'budget_prevu' => 'nullable|numeric',
    //         // 'calcule' => 'boolean',            
    //         // 'regle.expression' => 'nullable|string',
    //     ]);

    //     $user = Auth::user();
    //     if ($request->has('variable_id')) {
    //         $var = Variable::findOrFail($validated['variable_id']);
    //         if($var->user_id !== $user->id){
    //             abort(401, 'Non autorisé, Lavariable specifié n\'appartient pas à cet utilisateur');
    //         }
    //         if(RegleCalcul::where('variable_id', $var->id)
    //                        ->exists()){
    //                     return response()->json('Cette variable a sa propre regle de calcul', 400);
    //                 }
    //     } 

    //     $sousVariable = SousVariable::create([
    //         'user_id'   => $user->id,
    //         'variable_id' => $validated['variable_id'] ?? null,
    //         'nom' => $validated['nom'],
    //         'budget_prevu' => $validated['budget_prevu'] ?? null,
    //         // 'regle_calcul' => $validated['regle']['expression'] ?? null,
    //     ]);
    //     // if ($sousVariable->calcule){
    //     //             return response()->json(['message' => 'Sous-variable calculés non encore pris en charge'], 400);
    //     // }

    //     return response()->json($sousVariable, 201);
    // } 
    public function store(Request $request)
    {
        $validated = $request->validate([
            'variable_id' => 'required|exists:categories,id', // le parent (niveau 2)
            'nom' => 'required|string',
            'budget_prevu' => 'nullable|numeric',
            // 'calcule' => 'boolean',
            // 'regle.expression' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Vérification que le parent est bien une variable (niveau 2)
        $variable = Categorie::findOrFail($validated['variable_id']);
        if ($variable->user_id !== $user->id || $variable->niveau !== 2) {
            abort(401, 'Non autorisé ou parent invalide');
        }

        // Vérifier si la variable a déjà une règle de calcul
        if ($variable->regleCalcul()->exists()) {
            return response()->json([
                'message' => 'Cette variable a sa propre règle de calcul, impossible d’ajouter une sous-variable'
            ], 400);
        }

        // Création de la sous-variable (niveau 3)
        $sousVariable = Categorie::create([
            'user_id'          => $user->id,
            'mois_comptable_id'=> $variable->mois_comptable_id,
            'parent_id'        => $variable->id,
            'nom'              => $validated['nom'],
            'budget_prevu'     => $validated['budget_prevu'] ?? null,
            'calcule'          => false, // par défaut non calculée
            'niveau'           => 3,
            'nature'           => $variable->nature, // hérite de la nature du parent
        ]);

        return response()->json($sousVariable, 201);
    }


    // 🔎 Afficher une sous-variable
    // public function show($id)
    // {
    //     $user = Auth::user() ; 

    //     $sv = SousVariable::findOrFail($id);
    
        
    //     if ($sv->user_id !== $user->id) {
    //         abort(401, 'Non autorisé');
    //     }  
    //     return SousVariable::with('variable')->findOrFail($id);
    // }

    // // ✏️ Mettre à jour une sous-variable
    // public function update(Request $request, $id)
    // {
    //     $sousVariable = SousVariable::findOrFail($id);

    //     $validated = $request->validate([
    //         'variable_id' => 'nullable|exists:variables,id',
    //         'nom' => 'nullable|string',
    //         'budget_prevu' => 'nullable|numeric',
    //         // 'regle.expression' => 'nullable|string',
    //     ]);
        

    //     $user = Auth::user();

    //     if($sousVariable->user_id !== $user->id){
    //             abort(401, 'Non autorisé, Lavariable specifié n\'appartient pas à cet utilisateur');
    //         }
    //     if ($validated['variable_id']) {
    //         $var = Variable::findOrFail($validated['variable_id']);
    //         if($var->user_id !== $user->id){
    //             abort(401, 'Non autorisé, Lavariable specifié n\'appartient pas à cet utilisateur');
    //         }
    //         if(RegleCalcul::where('variable_id', $var->id)
    //                        ->exists()){
    //                     return response()->json('Cette variable a sa propre regle de calcul', 400);
    //                 }
    //     } 



    //     $sousVariable->update([
    //         'variable_id' => $validated['variable_id'] ?? null,
    //         'nom' => $validated['nom'] ?? $sousVariable->nom,
    //         'budget_prevu' => $validated['budget_prevu'] ?? $sousVariable->budget_prevu,
    //         // 'regle_calcul' => $validated['regle']['expression'] ?? $sousVariable->regle_calcul,
    //     ]);

    //     return response()->json($sousVariable);
    // }

    // // Supprimer une sous-variable
    // public function destroy($id)
    // {
    //     $sousVariable  = SousVariable::findOrFail($id);
    //     // $this->authorize('delete', $sousVariable);
    //     $user = Auth::user();

    //     if($sousVariable->user_id !== $user->id){
    //             abort(401, 'Non autorisé, Lavariable specifié n\'appartient pas à cet utilisateur');
    //         }
    //     $regleCalcul = new ReglesCalculService();
    //     $sousParente = $regleCalcul->sousVariableRegleCalcul($sousVariable );

    //     $Parente = Variable::where('id', $sousParente)->first();
    //     if ($Parente) {
    //         throw new Exception("Cette sous-variable est déjà utilisée dans la règle de : " . $Parente->nom);
    //     }

    //     SousVariable::destroy($id);
    //     return response()->json(['message' => 'Sous-variable supprimée avec succès']);
    // }

    public function show($id)
    {
        $user = Auth::user();

        // Charger la sous-variable
        $sousVariable = Categorie::findOrFail($id);

        // Vérification que c’est bien une sous-variable
        if ($sousVariable->niveau !== 3) {
            abort(400, 'Cette catégorie n’est pas une sous-variable');
        }

        // Vérification autorisation
        if ($sousVariable->user_id !== $user->id) {
            abort(401, 'Non autorisé');
        }

        // Retour avec la variable parent
        return Categorie::with('parent.regleCalcul')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $sousVariable = Categorie::findOrFail($id);

        $validated = $request->validate([
            'parent_id'     => 'nullable|exists:categories,id', // variable parent
            'nom'           => 'nullable|string',
            'budget_prevu'  => 'nullable|numeric',
        ]);

        $user = Auth::user();

        // Vérification que c’est bien une sous-variable
        if ($sousVariable->niveau !== 3) {
            abort(400, 'Cette catégorie n’est pas une sous-variable');
        }

        // Vérification autorisation
        if ($sousVariable->user_id !== $user->id) {
            abort(401, 'Non autorisé');
        }

        // Vérification du parent si fourni
        if (!empty($validated['parent_id'])) {
            $variable = Categorie::findOrFail($validated['parent_id']);
            if ($variable->user_id !== $user->id || $variable->niveau !== 2) {
                abort(401, 'Non autorisé ou parent invalide');
            }

            // Empêcher la création si la variable a déjà une règle de calcul
            if ($variable->regleCalcul()->exists()) {
                return response()->json('Cette variable a sa propre règle de calcul', 400);
            }
        }

        // Mise à jour
        $sousVariable->update([
            'parent_id'     => $validated['parent_id'] ?? $sousVariable->parent_id,
            'nom'           => $validated['nom'] ?? $sousVariable->nom,
            'budget_prevu'  => $validated['budget_prevu'] ?? $sousVariable->budget_prevu,
        ]);

        return response()->json($sousVariable->load('parent'));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $sousVariable = Categorie::findOrFail($id);
            $user = Auth::user();

            // Vérification que c’est bien une sous-variable
            if ($sousVariable->niveau !== 3) {
                DB::rollBack();
                return response()->json(['message' => 'Cette catégorie n’est pas une sous-variable'], 400);
            }

            // Vérification autorisation
            if ($sousVariable->user_id !== $user->id) {
                DB::rollBack();
                return response()->json(['message' => 'Non autorisé'], 401);
            }

            // Vérification règle de calcul
            // $regleCalcul = new ReglesCalculService();
            // $parente = $regleCalcul->sousVariableRegleCalcul($sousVariable);

            // if ($parente) {
            //     DB::rollBack();
            //     return response()->json([
            //         'message' => "Cette sous-variable est déjà utilisée dans la règle de calcul de : " . $parente->nom
            //     ], 400);
            // }

            // Suppression
            $sousVariable->delete();

            DB::commit();

            return response()->json(['message' => 'Sous-variable supprimée avec succès'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la suppression de la sous-variable',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}
