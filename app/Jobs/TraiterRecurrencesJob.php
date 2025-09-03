<?php

namespace App\Jobs;

use App\Models\Recurrence;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TraiterRecurrencesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $today = now()->startOfDay();

        $recurrences = Recurrence::whereDate('date_debut', '<=', $today)
            ->where(function($q) use ($today) {
                $q->whereNull('date_fin')
                ->orWhereDate('date_fin', '>=', $today);
            })
            ->get();

        foreach ($recurrences as $recurrence) {
            if ($this->doitExecuter($recurrence, $today)) {
                $this->appliquerRecurrence($recurrence, $today);
            }
        }
    }
    protected function doitExecuter(Recurrence $recurrence, Carbon $date): bool
    {
        $diff = $recurrence->date_debut->diffInDays($date);

        return match ($recurrence->frequence) {
            'quotidien' => $diff % $recurrence->interval === 0,
            'hebdo'     => $diff % (7 * $recurrence->interval) === 0,
            'mensuel'   => $date->diffInMonths($recurrence->date_debut) % $recurrence->interval === 0,
            'annuel'    => $date->diffInYears($recurrence->date_debut) % $recurrence->interval === 0,
        };
    }

    protected function appliquerRecurrence(Recurrence $recurrence, Carbon $date)
    {
        // Exemple si lié à une opération :
        if ($recurrence->operation_id) {
            $operation = $recurrence->operation;
            $nouvelle = $operation->replicate();
            $nouvelle->date = $date;
            $nouvelle->save();
        }
    }

    // Idem pour tableau / variable / sous_variable
}
