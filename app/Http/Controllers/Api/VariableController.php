<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoisComptable;
use App\Models\Tableau;
use App\Models\Variable;
use App\Services\RegleCalculService;
use App\Services\ReglesCalculService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VariableController extends Controller
{
    //
    public function index()
{
    $user = Auth::user();

    $VariablesSortie = $user
        ->tableaux()->where('nature', 'sortie')
        ->with(['variables.sousVariables', 'variables.regleCalcul']) // eager loading
        ->get()
        ->pluck('variables')
        ->flatten();

    $VariablesEntree = $user
        ->tableaux()->where('nature', 'entree')
        ->with(['variables.sousVariables', 'variables.regleCalcul'])
        ->get()
        ->pluck('variables')
        ->flatten();

    return response()->json([
        'message' => 'Liste de vos Variables',
        'sorties' => $VariablesSortie,
        'entrees' => $VariablesEntree,
    ], 200);
    }
    // 🔍 Liste des variables pour un tableau donné
    public function indexByTableau($tableauId)
    {
        $user = Auth::user();
        $tableau = Tableau::where('id', $tableauId)
                          ->where('user_id', $user->id)
                          ->exists();
        if($tableau) {
                    return $tableau->variables()->with('sousVariables', 'operations')->get();

        }else {
            return response()->json(['message' => 'Tableau non trouvé ou non autorisé'], 404);
        }
    }


    // Créer une variable dans un tableau
    public function store(Request $request, ReglesCalculService $validator)
    {
        $validated = $request->validate([
            'tableau_id' => 'required|exists:tableaus,id',
            'nom' => 'required|string',
            'type' => 'required|in:simple,sous-tableau',
            'budget_prevu' => 'nullable|numeric',               
            'calcule' => 'boolean',            
            'regle.expression' => 'nullable|string',

            // Si type = sous-tableau
            'sous_variables' => 'required_if:type,sous-tableau|array',
            'sous_variables.*.nom' => 'required|string',
            'sous_variables.*.budget_prevu' => 'nullable|numeric',
            'sous_variables.*.calcule' => 'boolean',            
            'sous_variables.*.regle.expression' => 'nullable|string',
        ]);
        
        //connected user
        
        // $mois = Tableau::where('user_id', $user->id)
        //                      ->where('id', $tableau->mois_comptable_id)
        //                      ->first();

        $user = Auth::user(); 

        $tableau = Tableau::findOrFail($validated['tableau_id']);

        
        
        if ($tableau->user_id !== $user->id) {
            abort(401, 'Non autorisé');
        }   
        
        if (Variable::where('tableau_id', $tableau->id)
                    ->where('nom', $validated['nom'])
                    ->exists()) {
            return response()->json([
                'message' => 'Une variable avec ce nom existe déjà pour ce tableau',
            ], 422);
        }

    //     $variable = DB::transaction(function () use ($validated, $validator, $user) {
    //     $variable = Variable::create([
    //         'user_id'       => $user->id,
    //         'tableau_id'    => $validated['tableau_id'],
    //         'nom'           => $validated['nom'],
    //         'type'          => $validated['type'],
    //         'budget_prevu'  => $validated['budget_prevu'] ?? null,
    //         'calcule'       => $validated['calcule'] ?? false,
    //         // 'regle_calcul'  => $validated['regle']['expression'] ?? null,
    //     ]);

    //     if ($variable->type === 'sous-tableau') {
    //         foreach ($validated['sous_variables'] ?? [] as $svData) {
    //             $sousvariable = $variable->sousVariables()->create([
    //                 'user_id'       => $user->id,
    //                 'nom'           => $svData['nom'],
    //                 'budget_prevu'  => $svData['budget_prevu'] ?? null,
    //                 'calcule'       => $svData['calcule'] ?? false,
    //                 // 'regle_calcul' => $svData['regle']['expression'] ?? null,
    //             ]);
    //             if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
    //                         $validator->validerExpression($svData['regle']['expression']);
    //                         $sousvariable->regleCalcul()->create([
    //                             'user_id'       => $user->id,
    //                             'expression' => $svData['regle']['expression'],
    //                     ]);
    //                 }//ne bouge pas cet acolade
    //         }
    //     } elseif ($variable->calcule) {
                        
    //         $validator->validerExpression($validated['regle']['expression']);
    //         $variable->regleCalcul()->create([
    //             'user_id'       => $user->id,
    //             'expression' => $validated['regle']['expression'],
    //         ]);

    //     }
    //     return $variable;
    // });


        $variable = DB::transaction(function () use ($validated, $validator, $user) {

            // 🔹 Validation AVANT la création
            if ($validated['type'] === 'sous-tableau') {
                foreach ($validated['sous_variables'] ?? [] as $svData) {
                    if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
                        $validator->validerExpression($svData['regle']['expression']);
                    }
                }
            } elseif ($validated['calcule']) {
                $validator->validerExpression($validated['regle']['expression']);
            }

            // 🔹 Création seulement après validation
            $variable = Variable::create([
                'user_id'       => $user->id,
                'tableau_id'    => $validated['tableau_id'],
                'nom'           => $validated['nom'],
                'type'          => $validated['type'],
                'budget_prevu'  => $validated['budget_prevu'] ?? null,
                'calcule'       => $validated['calcule'] ?? false,
            ]);

            if ($variable->type === 'sous-tableau') {
                foreach ($validated['sous_variables'] ?? [] as $svData) {
                    $sousvariable = $variable->sousVariables()->create([
                        'user_id'       => $user->id,
                        'nom'           => $svData['nom'],
                        'budget_prevu'  => $svData['budget_prevu'] ?? null,
                        'calcule'       => $svData['calcule'] ?? false,
                    ]);

                    if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
                        $sousvariable->regleCalcul()->create([
                            'user_id'   => $user->id,
                            'expression'=> $svData['regle']['expression'],
                        ]);
                    }
                }
            } elseif ($variable->calcule) {
                $variable->regleCalcul()->create([
                    'user_id'   => $user->id,
                    'expression'=> $validated['regle']['expression'],
                ]);
            }

            return $variable;
        });

    return $variable->load('regleCalcul', 'sousVariables.regleCalcul');
    }



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
        $variable  = Variable::findOrFail($id);
        // $this->authorize('delete', $variable);

        $user = Auth::user() ; 

        $tableau = Tableau::findOrFail($variable->tableau_id);

        
        // $mois = MoisComptable::where('user_id', $user->id)
        //                      ->where('id', $tableau->mois_comptable_id)
        //                      ->first();

        if ($tableau->user_id !== $user->id) {
            abort(401, 'Non autorisé');
        }   
        
        
        
        $regleCalcul = new RegleCalculService();
        $parente = $regleCalcul->variableRegleCalcul($variable);
        if ($parente) { 
            throw new Exception("Cette variable est déjà utilisée dans la règle de calcul de : " . $parente->nom);
        }
        Variable::destroy($id);
        return response()->json(['message' => 'Variable supprimée avec succès']); 
    }

}
