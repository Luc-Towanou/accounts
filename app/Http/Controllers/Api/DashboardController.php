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
        // $mois = MoisComptable::whereMonth('date_debut', now()->month)
        //                       ->whereMonth('date_fin', now()->month)
        //                       ->whereYear('date_debut', now()->year) // date_debut > n
        //                       ->whereYear('date_fin', now()->year)
        //                       ->first();

        $now = now()->startOfDay();

        $mois = MoisComptable::where('date_debut', '<=', $now)
                            ->where('date_fin', '>=', $now)
                            ->first();

        if (!$mois) {
            return response()->json([
                'error' => 'Aucun mois trouvé pour le mois en cours.'
            ], 404);
        }

        // Solde global
        $solde = $mois->montant_net;

        // Totaux revenus/dépenses
        $revenus = $mois->gains_reelle;
        $depenses = $mois->depense_reelle;

        // Pourcentage du budget utilisé
        // $pourcentage_budget = $mois->pourcentage_budget;
        $depense_totale = $mois->tableaux()->where('nature', 'sortie')->sum('depense_reelle');
        $budget_prevu_total = $mois->tableaux()->sum('budget_prevu');

        $pourcentage_budget = $budget_prevu_total > 0
            ? ($depense_totale / $budget_prevu_total) * 100
            : 0;

        // 3 plus gros tableaux de dépenses
        // $top_categories = DB::table('tableaus')
        //     ->join('variables', 'variables.tableau_id', '=', 'tableaus.id')
        //     ->join('sous_variables', 'sous_variables.variable_id', '=', 'variables.id')
        //     ->join('operations', 'operations.sous_variable_id', '=', 'sous_variables.id')
        //     ->where('tableaus.mois_comptable_id', $mois->id)
        //     ->where('operations.nature', 'sortie')
        //     ->select('tableaus.nom', DB::raw('SUM(operations.montant) as total_depense'))
        //     ->groupBy('tableaus.nom')
        //     ->orderByDesc('total_depense')
        //     ->limit(3)
        //     ->get();


        $moisId = $mois->id;

        // Sous-variables
        $opsSousVariables = DB::table('operations')
            ->join('sous_variables', 'operations.sous_variable_id', '=', 'sous_variables.id')
            ->join('variables', 'sous_variables.variable_id', '=', 'variables.id')
            ->join('tableaus', 'variables.tableau_id', '=', 'tableaus.id')
            ->where('operations.nature', 'sortie')
            ->where('tableaus.mois_comptable_id', $moisId)
            ->select(
                'tableaus.id as tableau_id',
                'tableaus.nom as tableau_nom',
                DB::raw("CONCAT(variables.nom, ' > ', sous_variables.nom) as categorie"),
                DB::raw('operations.montant')
            );

        // Variables directes
        $opsVariables = DB::table('operations')
            ->join('variables', 'operations.variable_id', '=', 'variables.id')
            ->join('tableaus', 'variables.tableau_id', '=', 'tableaus.id')
            ->where('operations.nature', 'sortie')
            ->where('tableaus.mois_comptable_id', $moisId)
            ->select(
                'tableaus.id as tableau_id',
                'tableaus.nom as tableau_nom',
                'variables.nom as categorie',
                DB::raw('operations.montant')
            );

        // Fusionner
        $allOps = DB::query()
            ->fromSub($opsSousVariables->unionAll($opsVariables), 'all_operations');

        // Regrouper par tableau et catégorie
        $grouped = DB::query()
            ->fromSub($allOps, 'grouped')
            ->select(
                'tableau_id',
                'tableau_nom',
                'categorie',
                DB::raw('SUM(montant) as total_categorie')
            )
            ->groupBy('tableau_id', 'tableau_nom', 'categorie');

        // Extraire les catégories dominantes par tableau
        $dominantes = DB::query()
            ->fromSub($grouped, 'dominantes')
            ->select('tableau_nom', 'categorie', 'total_categorie')
            ->orderByDesc('total_categorie')
            ->get();

        // Finalement, on récupère les 3 tableaux les plus dépensiers
        $topTableaux = DB::query()
            ->fromSub($grouped, 'totaux')
            ->select('tableau_nom', DB::raw('SUM(total_categorie) as total_depense'))
            ->groupBy('tableau_nom')
            ->orderByDesc('total_depense')
            ->limit(3)
            ->get();


        return response()->json([
            'solde' => $solde,
            'revenus' => $revenus,
            'depenses' => $depenses,
            'pourcentage_budget' => $pourcentage_budget,  //. '%',
            'top_tableaux' => $topTableaux,
            'categories_dominantes' => $dominantes,
        ]);
    }
}
