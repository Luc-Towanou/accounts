<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Depense;
use Illuminate\Support\Facades\Auth;
use App\Notifications\GoalProgressNotification;
use Illuminate\Support\Facades\Notification;

class GoalService
{
    /**
     * Créer un nouvel objectif
     */
    public function createGoal(array $data)
    {
        $goal = Goal::create([
            'user_id'     => Auth::id(),
            'title'       => $data['title'],
            'target_amount'      => $data['target_amount'],
            'periode'      => $data['periode'],
            'type'        => $data['type'],  
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
        ]);

        return $goal;
    }

    /**
     * Modifier un objectif existant
     */
    public function updateGoal(Goal $goal, array $data)
    {
        $goal->update($data);
        return $goal;
    }

    /**
     * Calculer la progression d’un objectif
     * => somme des dépenses liées à l’objectif / montant de l’objectif
     */
    public function calculateProgress(Goal $goal)
    {
        // Déterminer les dépenses liées à cet objectif
        $depense_reelle = Depense::where('user_id', $goal->user_id)
            ->whereBetween('date', [$goal->start_date, $goal->end_date]);

        // Selon le type, filtrer sur la variable/sous-variable/tableau
        if ($goal->type === 'variable' && $goal->variable_id) {
            $depense_reelle->where('variable_id', $goal->variable_id);
        }

        if ($goal->type === 'sous_variable' && $goal->sous_variable_id) {
            $depense_reelle->where('sous_variable_id', $goal->sous_variable_id);
        }

        // total des dépenses
        $totalDepense_reelle = $depense_reelle->sum('amount');

        // progression en %
        $progress = ($totalDepense_reelle / $goal->amount) * 100;

        return [
            'total_depense' => $totalDepense_reelle,
            'progress'       => min($progress, 100), // on limite à 100%
        ];
    }

    /**
     * Suivre l’évolution de l’objectif
     * => renvoyer une timeline ou des stats mensuelles
     */
    public function trackEvolution(Goal $goal)
    {
        $depense_reelle = Depense::where('user_id', $goal->user_id)
            ->whereBetween('date', [$goal->start_date, $goal->end_date])
            ->orderBy('date')
            ->get();

        $evolution = [];

        foreach ($depense_reelle as $depense_reelle) {
            $evolution[] = [
                'date'   => $depense_reelle->date,
                'amount' => $depense_reelle->amount,
                'cumulative' => $depense_reelle->where('date', '<=', $depense_reelle->date)->sum('amount'),
            ];
        }

        return $evolution;
    }

    /**
     * Vérifier si l’objectif est atteint
     */
    public function isGoalAchieved(Goal $goal)
    {
        $progress = $this->calculateProgress($goal);
        return $progress['progress'] >= 100;
    }

    /**
     * Vérifier et notifier sur les étapes atteintes (50%, 80%, 100%)
     */
    public function checkMilestones(Goal $goal)
    {
        $progress = $this->calculateProgress($goal)['progress'];
        $milestones = [50, 80, 100];

        foreach ($milestones as $milestone) {
            if ($progress >= $milestone && !$goal->notifications()->where('milestone', $milestone)->exists()) {
                // Ici, tu peux créer une notification
                $goal->notifications()->create([
                    'milestone' => $milestone,
                    'message'   => "Félicitations ! Vous avez atteint $milestone% de votre objectif : {$goal->title}",
                ]);
            }
        }
    }
     /**
     * Supprimer un objectif
     */
    public function deleteGoal(Goal $goal)
    {
        return $goal->delete();
    }

    // (Méthode calculateProgress supprimée car elle était dupliquée)

    /**
     * Suivre l’évolution d’un objectif
     */
    public function trackGoal(Goal $goal)
    {
        $progress = $this->calculateProgress($goal);

        return [
            'goal' => $goal->name,
            'target_amount' => $goal->target_amount,
            'period' => $goal->period,
            'progress' => $progress,
            'status' => $this->checkIfAchieved($goal) ? 'Atteint' : 'En cours',
        ];
    }

    /**
     * Vérifier si un objectif est atteint
     */
    public function checkIfAchieved(Goal $goal)
    {
        $progress = $this->calculateProgress($goal);

        return $progress >= 100;
    }

    /**
     * Notifier l’utilisateur quand il atteint certains paliers
     */
    public function notifyUser(Goal $goal)
    {
        $progress = $this->calculateProgress($goal);

        $thresholds = [50, 80, 100];

        foreach ($thresholds as $threshold) {
            if ($progress >= $threshold && !$goal->notifications()->where('threshold', $threshold)->exists()) {
                // Envoie de la notification
                Notification::send($goal->user, new GoalProgressNotification($goal, $threshold));

                // Enregistrer que ce palier a été atteint pour ne pas notifier plusieurs fois
                $goal->notifications()->create([
                    'threshold' => $threshold,
                ]);
            }
        }

    }
}    
