<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Operation;
use App\Models\Tableau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    //
     /**
     * GET /stats/categories
     * Retourne le total dépensé/reçu par catégorie
     */
    public function categories(Request $request)
    {
        $moisId = $request->input('mois_comptable_id');

        $stats = Operation::select(
                'categorie_id',
                DB::raw('SUM(montant) as total')
            )
            ->when($moisId, fn($q) => $q->where('mois_comptable_id', $moisId))
            ->groupBy('categorie_id')
            ->with('categorie:id,nom,couleur,icone,type')
            ->get()
            ->map(function ($item) {
                return [
                    'categorie' => $item->categorie->nom ?? 'Sans catégorie',
                    'type'      => $item->categorie->type ?? null,
                    'total'     => (float) $item->total,
                    'couleur'   => $item->categorie->couleur ?? null,
                    'icone'     => $item->categorie->icone ?? null,
                ];
            });

        return response()->json($stats);
    }

    /**
     * GET /stats/variables
     * Retourne une analyse détaillée par tableau/variable/sous-variable
     */
    public function variables(Request $request)
    {
        $moisId = $request->input('mois_comptable_id');

        $tableaux = Tableau::with([
            'variables.sousVariables.operations' => function ($q) use ($moisId) {
                $q->when($moisId, fn($query) => $query->where('mois_comptable_id', $moisId));
            },
            'variables.operations' => function ($q) use ($moisId) {
                $q->when($moisId, fn($query) => $query->where('mois_comptable_id', $moisId));
            }
        ])
        ->when($moisId, fn($q) => $q->where('mois_comptable_id', $moisId))
        ->get();

        $result = $tableaux->map(function ($tableau) {
            return [
                'id'            => $tableau->id,
                'nom'           => $tableau->nom,
                'total_budget'  => (float) $tableau->variables->sum('budget_prevu'),
                'total_depense' => (float) $tableau->variables->sum(function ($v) {
                    return $v->operations->sum('montant') + $v->sousVariables->sum(function ($sv) {
                        return $sv->operations->sum('montant');
                    });
                }),
                'variables'     => $tableau->variables->map(function ($variable) {
                    return [
                        'id'              => $variable->id,
                        'nom'             => $variable->nom,
                        'budget_prevu'    => (float) $variable->budget_prevu,
                        'depense_reelle'  => (float) $variable->operations->sum('montant') 
                                             + $variable->sousVariables->sum(fn($sv) => $sv->operations->sum('montant')),
                        'sous_variables'  => $variable->sousVariables->map(function ($sv) {
                            return [
                                'id'             => $sv->id,
                                'nom'            => $sv->nom,
                                'budget_prevu'   => (float) $sv->budget_prevu,
                                'depense_reelle' => (float) $sv->operations->sum('montant'),
                            ];
                        })
                    ];
                })
            ];
        });

        return response()->json(['tableaux' => $result]);
    }
}
