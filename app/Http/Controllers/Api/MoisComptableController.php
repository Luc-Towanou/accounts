<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoisComptable;
use App\Models\User;
use App\Services\ReglesCalculService;
use Barryvdh\DomPDF\Facade\Pdf;
// use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// use PDF;
use Exception;

class MoisComptableController extends Controller
{
    //
    // public function store

      //
    public function index()
    {
    
        $user = Auth::user();
        // Récupérer les mois comptables de l'utilisateur
        $mois = $user->moisComptables;


    return response()->json([
        'message' => 'Liste de vos mois comptables',
        'mois' => $mois
    ],200);
    }
    public function store(Request $request, ReglesCalculService $validator)
    {
        $validated = $request->validate([
            'mois' => 'required|string',
            'annee' => 'required|integer',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'budget_prevu' => 'nullable|numeric',

            'tableaux' => 'nullable|array',
            'tableaux.*.nom' => 'required_with:tableaux|string',
            'tableaux.*.budget_prevu' => 'nullable|numeric',
            'tableaux.*.nature' => 'required_with:tableaux|in:entree,sortie',

            'tableaux.*.variables' => 'nullable|array',
            'tableaux.*.variables.*.nom' => 'required_with:tableaux.*.variables|string',
            'tableaux.*.variables.*.type' => 'required_with:tableaux.*.variables|in:simple,sous-tableau',
            'tableaux.*.variables.*.budget_prevu' => 'nullable|numeric',
            'tableaux.*.variables.*.calcule' => 'boolean',
            'tableaux.*.variables.*.regle.expression' => 'nullable|string',

            'tableaux.*.variables.*.sous_variables' => 'nullable|array',
            'tableaux.*.variables.*.sous_variables.*.nom' => 'required_with:tableaux.*.variables.*.sous_variables|string',
            'tableaux.*.variables.*.sous_variables.*.budget_prevu' => 'nullable|numeric',
            'tableaux.*.variables.*.sous_variables.*.calcule' => 'boolean',
            'tableaux.*.variables.*.sous_variables.*.regle.expression' => 'nullable|string',
        ]);
        // Utilisateur connecté  
            // $user = auth()->user() ;
            $user = Auth::user();

            $ancienMois = MoisComptable::where('user_id', $user->id)
                                    ->where('mois', $validated['mois'])
                                    ->where('annee', $validated['annee'])
                                    ->first();
            if ($ancienMois) { 
                return response()->json([
                    'message' => 'Un mois comptable portant ce nom existe déjà',
                ], 409);
            }
        try {
            DB::transaction(function () use ($validated, $user, $validator) {

                // 1. Validation TOUTES les expressions avant création
                foreach ($validated['tableaux'] ?? [] as $tableauData) {
                    foreach ($tableauData['variables'] ?? [] as $varData) {
                        if (($varData['calcule'] ?? false) && isset($varData['regle']['expression'])) {
                            $validator->validerExpression($varData['regle']['expression']);
                        }
                        if ($varData['type'] === 'sous-tableau') {
                            foreach ($varData['sous_variables'] ?? [] as $sousVarData) {
                                if (($sousVarData['calcule'] ?? false) && isset($sousVarData['regle']['expression'])) {
                                    $validator->validerExpression($sousVarData['regle']['expression']);
                                }
                            }
                        }
                    }
                }

                // 2. Création effective après validation
                $mois = $user->moisComptables()->create([
                    'mois' => $validated['mois'],
                    'annee' => $validated['annee'],
                ]);

                foreach ($validated['tableaux'] ?? [] as $tableauData) {
                    $tableau = $mois->tableaux()->create([
                        'user_id' => $user->id,
                        'nom' => $tableauData['nom'],
                        'budget_prevu' => $tableauData['budget_prevu'] ?? null,
                        'nature' => $tableauData['nature'],
                    ]);

                    foreach ($tableauData['variables'] ?? [] as $varData) {
                        if ($varData['type'] === 'sous-tableau') {
                            $variable = $tableau->variables()->create([
                                'user_id' => $user->id,
                                'nom' => $varData['nom'],
                                'budget_prevu' => $varData['budget_prevu'] ?? null,
                                'type' => 'sous-tableau',
                            ]);

                            foreach ($varData['sous_variables'] ?? [] as $sousVarData) {
                                $sousVar = $variable->sousVariables()->create([
                                    'user_id' => $user->id,
                                    'nom' => $sousVarData['nom'],
                                    'calcule' => $sousVarData['calcule'] ?? false,
                                    'budget_prevu' => $sousVarData['budget_prevu'] ?? null,
                                ]);

                                if (($sousVarData['calcule'] ?? false) && isset($sousVarData['regle']['expression'])) {
                                    $sousVar->regleCalcul()->create([
                                        'expression' => $sousVarData['regle']['expression'],
                                    ]);
                                }
                            }

                        } else {
                            $variable = $tableau->variables()->create([
                                'user_id' => $user->id,
                                'nom' => $varData['nom'],
                                'type' => 'simple',
                                'calcule' => $varData['calcule'] ?? false,
                                'budget_prevu' => $varData['budget_prevu'] ?? null,
                            ]);

                            if (($varData['calcule'] ?? false) && isset($varData['regle']['expression'])) {
                                $variable->regleCalcul()->create([
                                    'expression' => $varData['regle']['expression'],
                                ]);
                            }
                        }
                    }
                }
            });

            return response()->json([
                'message' => 'Mois comptable créé avec succès 🎉',
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Impossible de créer le mois',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function show(MoisComptable $moisComptable)
    {
        $user = Auth::user();
        if($moisComptable->user_id !== $user->id){
            return response()->json("Non auturisé", 401);
        }
        // $this->authorize('view', $moisComptable);
        return $moisComptable->load('tableaux.variables');
    }

    public function update(Request $request, MoisComptable $moisComptable)
    {
        // $this->authorize('update', $moisComptable);

        $user = Auth::user();
        if($moisComptable->user_id !== $user->id){
            return response()->json("Non auturisé", 401);
        }
        $request->validate([
            'mois' => 'nullable|string',
            'annee' => 'nullable|integer',
        ]);

        if ($request->has('mois')) $moisComptable->mois = $request->mois;
        if ($request->has('annee')) $moisComptable->annee = $request->annee;
        $moisComptable->save();

        // $moisComptable->update($validated);
        return $moisComptable->fresh();
    }
    public function destroy(MoisComptable $moisComptable)
    {
        $user = Auth::user();
        if($moisComptable->user_id !== $user->id){
            return response()->json("Non auturisé", 401);
        }
        // $this->authorize('delete', $moisComptable);
        $moisComptable->delete();
        return response()->noContent();
    }


    public function exportMoisPDF($id)
    {
        $mois = MoisComptable::with([
            'tableaux.variables.sousVariables.operations',
            'tableaux.variables.regleCalcul',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.mois', compact('mois'))
                ->setPaper('A4', 'portrait');

        return $pdf->download('mois_comptable_'.$mois->id.'.pdf');
    }


public function exportPdf($id)
{
    $mois = MoisComptable::with([
        'tableaux.variables.sousVariables.operations'
    ])->findOrFail($id);

    // Calculs pour l'analyse financière
    $totalBudget = 0;
    $totalDepense = 0;
    $categories = [];

    foreach ($mois->tableaux as $tableau) {
        foreach ($tableau->variables as $variable) {
            $budget = $variable->budget_prevu ?? 0;
            $depense = $variable->depense_reelle ?? 0;

            $totalBudget += $budget;
            $totalDepense += $depense;

            $categories[] = [
                'nom' => $variable->nom,
                'budget' => $budget,
                'depense' => $depense,
                'ecart' => $budget - $depense,
                'taux_depense' => $budget > 0 ? round(($depense / $budget) * 100, 1) : 0
            ];
        }
    }

    $analyse = [
        'totalBudget' => $totalBudget,
        'totalDepense' => $totalDepense,
        'solde' => $totalBudget - $totalDepense,
        'categories' => $categories,
        'tauxUtilisation' => $totalBudget > 0 ? round(($totalDepense / $totalBudget) * 100, 1) : 0
    ];

    // Génération du PDF
    $pdf = Pdf::loadView('pdf.mois2', compact('mois', 'analyse'))
              ->setPaper('a4', 'portrait');

    return $pdf->download("MoisComptable-{$mois->nom}.pdf");
}
/**
 * Mois comptable actif de l'utilisateur connecté
 */
    public function mois_actif() {
        $user = Auth::user(); 
        $moisActif = MoisComptable::where('user_id', $user->id)
                                ->where('annee', now()->year)
                                ->where('mois', now()->locale('fr')->monthName)
                                ->first();
        // $last = MoisComptable::where('user_id', $user->id)
        //                         ->orderBy('id', 'desc')                        
        //                         ->first();
        // dd([ 'mois_actif' => $moisActif,
        //             'last' => $last,
        //            'now' => now()->locale('fr')->monthName,] );
        if(!$moisActif) return response()->json('Empty', 422) ;
        return response()->json([
            'Mois comptable en cours' => $moisActif->mois,
            'mois'                    => $moisActif->load('tableaux.variables.sousVariables', 'tableaux.variables.regleCalcul'),
        ], 200) ; 



    }





}
