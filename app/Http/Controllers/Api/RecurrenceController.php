<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recurrence;
use App\Models\RecurrenceLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecurrenceController extends Controller
{
    //
    // ✅ Liste toutes les récurrences
    public function index()
    {
        return Recurrence::with(['tableau', 'variable', 'sousVariable', 'operation'])->get();
    }

    // ✅ Crée une récurrence
    public function store(Request $request)
    {
        $data = $request->validate([
            'tableau_id' => 'nullable|exists:tableaux,id',
            'variable_id' => 'nullable|exists:variables,id',
            'sous_variable_id' => 'nullable|exists:sous_variables,id',
            'operation_id' => 'nullable|exists:operations,id',
            'frequence' => ['required', Rule::in(['quotidien', 'hebdo', 'mensuel', 'annuel'])],
            'interval' => 'required|integer|min:1',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        // Vérification logique "exactement 1 des 4 IDs est défini"
        $ids = collect([$data['tableau_id'] ?? null, $data['variable_id'] ?? null, $data['sous_variable_id'] ?? null, $data['operation_id'] ?? null]);
        if ($ids->filter(fn($v) => !is_null($v))->count() !== 1) {
            return response()->json(['error' => 'Exactement un des 4 champs (tableau, variable, sous_variable, operation) doit être défini.'], 422);
        }

        $recurrence = Recurrence::create($data);
        return response()->json($recurrence, 201);
    }

    // ✅ Affiche une récurrence
    public function show(Recurrence $recurrence)
    {
        return $recurrence->load(['tableau', 'variable', 'sousVariable', 'operation']);
    }

    // ✅ Met à jour une récurrence
    public function update(Request $request, Recurrence $recurrence)
    {
        $data = $request->validate([
            'tableau_id' => 'nullable|exists:tableaux,id',
            'variable_id' => 'nullable|exists:variables,id',
            'sous_variable_id' => 'nullable|exists:sous_variables,id',
            'operation_id' => 'nullable|exists:operations,id',
            'frequence' => ['sometimes', Rule::in(['quotidien', 'hebdo', 'mensuel', 'annuel'])],
            'interval' => 'sometimes|integer|min:1',
            'date_debut' => 'sometimes|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        $ids = collect([
            $data['tableau_id'] ?? $recurrence->tableau_id,
            $data['variable_id'] ?? $recurrence->variable_id,
            $data['sous_variable_id'] ?? $recurrence->sous_variable_id,
            $data['operation_id'] ?? $recurrence->operation_id
        ]);
        if ($ids->filter(fn($v) => !is_null($v))->count() !== 1) {
            return response()->json(['error' => 'Exactement un des 4 champs (tableau, variable, sous_variable, operation) doit être défini.'], 422);
        }

        $recurrence->update($data);
        return response()->json($recurrence);
    }

    // ✅ Supprime une récurrence
    public function destroy(Recurrence $recurrence)
    {
        $recurrence->delete();
        return response()->json(null, 204);
    }

    public function appliquer(Request $request, Recurrence $recurrence)
    {
        $date = $request->input('date', now()->toDateString());

        $log = RecurrenceLog::firstOrCreate(
            ['recurrence_id' => $recurrence->id, 'date_execution' => $date],
            ['appliquee' => false]
        );

        if ($log->appliquee) {
            return response()->json(['message' => 'Déjà appliquée.'], 400);
        }

        // Création effective de l’opération
        if ($recurrence->operation_id) {
            $operation = $recurrence->operation->replicate();
            $operation->date = $date;
            $operation->save();
        }

        $log->appliquee = true;
        $log->save();

        return response()->json(['message' => 'Récurrence appliquée avec succès.']);
    }

}
