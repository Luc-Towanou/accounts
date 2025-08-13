<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoisComptable;
use App\Models\Tableau;
use App\Models\User;
use App\Services\ReglesCalculService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TableauController extends Controller
{
    //

    // 
    public function index()
    {
        $user = Auth::user();
        $tableauSortie = $user->tableaux()
                                          ->where('nature', 'sortie')
                                          ->with('variables', 'variables.sousVariables', 'variables.regleCalcul')->get();
        $tableauEntree = $user->tableaux()
                                          ->where('nature', 'entree')
                                          ->with('variables', 'variables.sousVariables', 'variables.regleCalcul')->get();
        
        return response()->json([
        'message' => 'Liste de vos tableaux',
        'sorties' => $tableauSortie,
        'entrees' => $tableauEntree,
    ],200);
    }

     // création avec variables et sous-variables intégrées
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

            'variables.*.sous_variables' => 'required_if:variables.*.type,sous-tableau|array',
            'variables.*.sous_variables.*.nom' => 'required|string',
            'variables.*.sous_variables.*.budget_prevu' => 'nullable|numeric',
            'variables.*.sous_variables.*.calcule' => 'boolean',            
            'variables.*.sous_variables.*.regle.expression' => 'nullable|string',
        ]);

        //connected user
        $user = Auth::user() ; 
        //verification appartenance du mois

        // if (!$request->user()) {
        // abort(403, 'Non authentifié');
        // }

            $mois = MoisComptable::whereKey($data['mois_comptable_id'])
                                 ->where('user_id', $user->id)
                                 ->firstOrFail();

        if (Tableau::where('mois_comptable_id', $mois->id)
                ->where('nom', $data['nom'])
                ->exists()) {
            return response()->json([
                'message' => 'Un tableau portant ce nom existe déjà pour ce mois comptable',
            ], 422);
        }
        try {    
            $tableau = DB::transaction(function () use ($data, $validator, $user) {
                
                // 1. Validation AVANT la création  
                foreach ($data['variables'] ?? [] as $vData) {
                    if (($vData['calcule'] ?? false) && isset($vData['regle']['expression'])) {
                        $validator->validerExpression($vData['regle']['expression']);
                    }
                    if ($vData['type'] === 'sous-tableau') {
                        foreach ($vData['sous_variables'] ?? [] as $svData) {
                            if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
                                $validator->validerExpression($svData['regle']['expression']);
                            }
                        }
                    }
                }

                // 2. Création Tableau + Variables + SousVariables  
                $tableau = Tableau::create([
                    'user_id'           => $user->id,
                    'mois_comptable_id' => $data['mois_comptable_id'],
                    'nom'               => $data['nom'],
                    'budget_prevu'      => $data['budget_prevu'] ?? null,
                    'nature'            => $data['nature'],
                ]);

                foreach ($data['variables'] ?? [] as $vData) {
                    if ($vData['type'] === 'sous-tableau') {
                        $variable = $tableau->variables()->create([
                            'user_id'       => $user->id,
                            'nom'           => $vData['nom'],
                            'budget_prevu'  => $vData['budget_prevu'] ?? null,
                            'type'          => 'sous-tableau',
                        ]);

                        foreach ($vData['sous_variables'] ?? [] as $svData) {
                            $sousVar = $variable->sousVariables()->create([
                                'user_id'       => $user->id,
                                'nom'           => $svData['nom'],
                                'calcule'       => $svData['calcule'] ?? false,
                                'budget_prevu'  => $svData['budget_prevu'] ?? null,
                            ]);

                            if (($svData['calcule'] ?? false) && isset($svData['regle']['expression'])) {
                                $sousVar->regleCalcul()->create([
                                    'user_id'   => $user->id,
                                    'expression'=> $svData['regle']['expression'],
                                ]);
                            }
                        }
                    } else {
                        $variable = $tableau->variables()->create([
                            'user_id'       => $user->id,
                            'nom'           => $vData['nom'],
                            'type'          => 'simple',
                            'calcule'       => $vData['calcule'] ?? false,
                            'budget_prevu'  => $vData['budget_prevu'] ?? null,
                        ]);

                        if (($vData['calcule'] ?? false) && isset($vData['regle']['expression'])) {
                            $variable->regleCalcul()->create([
                                'user_id'   => $user->id,
                                'expression'=> $vData['regle']['expression'],
                            ]);
                        }
                    }
                }
                return $tableau;
            });
            return $tableau->load('variables', 'variables.sousVariables', 'variables.regleCalcul');
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Impossible de créer le mois',
                'error'   => $e->getMessage()
            ], 422);
        }

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
    }

    public function show($id)
    {
        $user = Auth::user();
        $tableau = Tableau::findOrFail($id);
        if($tableau->user_id !== $user->id){
            return response()->json("Non auturisé", 401);
        }
        return Tableau::with('variables', 'variables.sousVariables', 'variables.regleCalcul')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => 'sometimes|string',
            'budget_prevu' => 'nullable|numeric',
            'nature' => 'nullable|in:entree,sortie',
        ]);
        $user = Auth::user();
        $tableau = Tableau::findOrFail($id);
        if($tableau->user_id !== $user->id){
            return response()->json("Non auturisé", 401);
        }
        // $tableau->update($data);
        if ($request->has('nom')) $tableau->nom = $request->nom;
        if ($request->has('budget_prevu')) $tableau->budget_prevu = $request->budget_prevu;
        if ($request->has('nature')) $tableau->nature = $request->nature;
        $tableau->save();

         return response()->json([
            'message' => 'Tableau mis à jour',
            'tableau' => $tableau,
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $tableau = Tableau::findOrFail($id);
        if($tableau->user_id !== $user->id){
            return response()->json("Non auturisé", 401);
        }
        Tableau::destroy($id);
        return response()->json(['message' => 'Tableau supprimé avec succès'], 200);
    }

}
