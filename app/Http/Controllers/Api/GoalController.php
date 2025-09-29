<?php

namespace App\Http\Controllers\Api;

use App\Services\GoalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class GoalController extends Controller
{
    protected $goalService;

    public function __construct(GoalService $goalService)
    {
        $this->goalService = $goalService;
    }

    /**
     * Créer un nouvel objectif
     */
    public function ajouter_goal(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'target_amount'      => 'required|numeric|min:0',
            'periode'     => 'required|in:jour,mois,annee',
            'type'        => 'required|in:tableau,variable,sous_variable',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'variable_id' => 'nullable|exists:variables,id',
            'sous_variable_id' => 'nullable|exists:sous_variables,id',
            'tableau_id' => 'nullable|exists:tableaus,id',
        ]);

        $goal = $this->goalService->createGoal(array_merge($validated, ['user_id' => Auth::id()]));

        return response()->json([
            'message' => 'Objectif créé avec succès',
            'data'    => $goal
        ], 201);
    }

    /**
     * Modifier un objectif
     */
    public function update(Request $request, $goalId)
    {
        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'amount'      => 'sometimes|numeric|min:0',
            'periode'     => 'sometimes|in:jour,mois,annee',
            'type'        => 'sometimes|in:tableau,variable,sous_variable',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'variable_id' => 'nullable|exists:variables,id',
            'sous_variable_id' => 'nullable|exists:sous_variables,id',
            'tableau_id' => 'nullable|exists:tableau,id',
        ]);

        $goal = $this->goalService->updateGoal($goalId, $validated);

        return response()->json([
            'message' => 'Objectif modifié avec succès',
            'data'    => $goal
        ], 200);
    }

    /**
     * Voir la progression d’un objectif
     */
    public function showProgress($goalId)
    {
        $progress = $this->goalService->calculateProgress($goalId);

        return response()->json([
            'message' => 'Progression de l’objectif',
            'data'    => $progress
        ], 200);
    }

    // /**
    //  * Suivre l’évolution d’un objectif
    //  */
    // public function track($goalId)
    // {
    //     $tracking = $this->goalService->trackGoal($goalId);

    //     return response()->json([
    //         'message' => 'Suivi de l’objectif',
    //         'data'    => $tracking
    //     ], 200);
    // }

    /**
     * Vérifier si un objectif est atteint
     */
    public function checkStatus($goalId)
    {
        $status = $this->goalService->checkIfAchieved($goalId);

        return response()->json([
            'message' => $status ? 'Objectif atteint 🎉' : 'Objectif en cours',
            'achieved' => $status
        ], 200);
    }

    /**
     * Liste des objectifs de l’utilisateur connecté
     */
    public function list_goal()
    {
        $goals = Auth::user()->goals;

        return response()->json([
            'message' => 'Liste des objectifs',
            'data'    => $goals
        ], 200);
    }

    /**
     * Supprimer un objectif
     */
    public function destroy($goalId)
    {
        $deleted = $this->goalService->deleteGoal($goalId);

        return response()->json([
            'message' => $deleted ? 'Objectif supprimé avec succès' : 'Erreur lors de la suppression'
        ], 200);
    }
    
}
