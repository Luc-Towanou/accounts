<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoisComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $mois = MoisComptable::whereMonth('date_debut', '<=', now()->month)
                              ->whereMonth('date_fin', '=>', now()->month)
                              ->whereYear('date_debut', '<=', now()->year) // date_debut > n
                              ->whereYear('date_fin', '=>', now()->year)
                              ->first();

        if (!$mois) {
            return response()->json([
                'error' => 'Aucun mois trouvé pour le mois en cours.'
            ], 404);
        }

        // Solde global
        $solde = $mois->solde;

        // Totaux revenus/dépenses
        $revenus = $mois->total_revenus;
        $depenses = $mois->total_depenses;

        // Pourcentage du budget utilisé
        // $pourcentage_budget = $mois->pourcentage_budget;
        $depense_totale = $mois->tableaux()->where('nature', 'sortie')->sum('depense_reelle');
        $budget_prevu_total = $mois->tableaux()->sum('budget_prevu');

        $pourcentage_budget = $budget_prevu_total > 0
            ? ($depense_totale / $budget_prevu_total) * 100
            : 0;

        // 3 plus grosses catégories de dépenses
        $top_categories = DB::table('tableaux')
            ->join('variables', 'variables.tableau_id', '=', 'tableaux.id')
            ->join('sous_variables', 'sous_variables.variable_id', '=', 'variables.id')
            ->join('operations', 'operations.sous_variable_id', '=', 'sous_variables.id')
            ->where('tableaux.mois_id', $mois->id)
            ->where('operations.type', 'depense')
            ->select('tableaux.nom', DB::raw('SUM(operations.montant) as total_depense'))
            ->groupBy('tableaux.nom')
            ->orderByDesc('total_depense')
            ->limit(3)
            ->get();

        return response()->json([
            'solde' => $solde,
            'revenus' => $revenus,
            'depenses' => $depenses,
            'pourcentage_budget' => $pourcentage_budget,
            'top_categories' => $top_categories
        ]);
    }
}
