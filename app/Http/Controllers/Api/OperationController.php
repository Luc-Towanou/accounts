<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Operation;
use App\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OperationController extends Controller
{
    //
   public function index()
    {
        $user = auth()->user();

        // Filtrer directement sur user_id dans variables ou sous_variables
        $sorties = Operation::with(['variable', 'sousVariable'])
            ->where('nature', 'sortie')
            ->where(function($q) use ($user) {
                $q->whereHas('variable', fn($qb) =>
                        $qb->where('user_id', $user->id)
                    )
                ->orWhereHas('sousVariable', fn($qb) =>
                        $qb->where('user_id', $user->id)
                    );
            })
            ->get();

        $entrees = Operation::with(['variable', 'sousVariable'])
            ->where('nature', 'entree')
            ->where(function($q) use ($user) {
                $q->whereHas('variable', fn($qb) =>
                        $qb->where('user_id', $user->id)
                    )
                ->orWhereHas('sousVariable', fn($qb) =>
                        $qb->where('user_id', $user->id)
                    );
            })
            ->get();

        return response()->json([
            'message' => 'Liste chargée',
            'sorties' => $sorties,
            'entrees' => $entrees,
        ]);
    }


 

    // index($variableId) — Voir les opérations d’une variable
    public function indexVariable($variableId)
    {
        $variable = Variable::with('operations')
                            ->findOrFail($variableId);

        return response()->json([
            'variable' => $variable->nom,
            'budget_prevu' => $variable->budget_prevu,
            'depense_reelle' => $variable->depense_reelle,
            'operations' => $variable->operations
        ]);
    }

    // Lister les opérations par variable
    public function indexByVariable($variableId)
    {
        $user = Auth::user();
        $variable = Variable::where('id', $variableId)
                            ->where('user_id', $user->id)
                            ->first();
        if($variable->type === 'sous-tableau') {
                    $operation = $variable->sousVariables()->operations()
                                                            ->with('sousVariavle');
        } elseif ($variable->type === 'simple') {
                    $operation = $variable->operations()
                                        ->with('variable');
        }
        
        return response()->json([ 'message' =>'Voici les operations des cette variables ',
                                    'operations' => $operation, ]);

    }

    // // Lister les opérations par sous-variable
    // public function indexBySousVariable($sousVariableId)
    // {
    //     return Operation::where('sous_variable_id', $sousVariableId)->get();
    // }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'montant'           => 'required|numeric|min:0',
            'nature'            => 'required|in:entree,sortie',
            'description'       => 'nullable|string',
            'date'              => 'nullable|date',
            'variable_id'       => 'nullable|exists:variables,id',
            'sous_variable_id'  => 'nullable|exists:sous_variables,id',
        ]);

        // 1.– Business validations hors transaction
        if (empty($validated['variable_id']) && empty($validated['sous_variable_id'])) {
            return response()->json([
                'error' => "L'opération doit être liée à une variable ou une sous-variable."
            ], 422);
        }

        if (! empty($validated['variable_id']) && ! empty($validated['sous_variable_id'])) {
            return response()->json([
                'error' => "Une opération ne peut pas appartenir à la fois à une variable et à une sous-variable."
            ], 422);
        }

        if (! empty($validated['variable_id'])) {
            $variable = Variable::findOrFail($validated['variable_id']);
            if ($variable->type === 'sous-tableau') {
                return response()->json([
                    'error' => "L'opération ne peut être directement relié à la variable elle même. Choisissez plutot une sous-variable."
                ], 422);
            }
        }

        // 2.– Transaction : création pure
        try {
            $operation = DB::transaction(function() use ($validated) {
                return Operation::create([
                    'montant'           => $validated['montant'],
                    'description'       => $validated['description'] ?? null,
                    'date'              => $validated['date'] ?? now(),
                    'nature'            => $validated['nature'],
                    'variable_id'       => $validated['variable_id'] ?? null,
                    'sous_variable_id'  => $validated['sous_variable_id'] ?? null,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error("Erreur lors de la création de l'opération : {$e->getMessage()}");
            return response()->json([
                'error' => "Une erreur est survenue lors de la création de l'opération."
            ], 500);
        }

        // 3.– Retour au client, hors transaction
        if (! empty($validated['variable_id'])) {
            return response()->json($operation->load('variable'), 201);
        }

        return response()->json($operation->load('sousVariable'), 201);
    }



    

      // 🔎 Afficher une opération
    public function show($id)
    {
        $user = Auth::user();
        $operation = Operation::findOrFail($id);
        if($operation->variable ) {
            $variable = $operation->variable;
            if($variable->user_id !== $user->id) {
                return response()->json("Vous n'est pas Autorisé à acceder à cette donnée", 401);
            }
        }
        if($operation->sousVariable ) {
            $sousVariable = $operation->sousVariable;
            if($sousVariable->user_id !== $user->id) {
                return response()->json("Vous n'est pas Autorisé à acceder à cette donnée", 401);
            }
        }
        return Operation::with(['variable', 'sousVariable'])->findOrFail($id);

                // return $operation->with(['variable', 'sousVariable']);
    }

    

    public function update(Request $request, $operationId)
    {
        $validated = $request->validate([
            'montant' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $operation = Operation::findOrFail($operationId);

        try {
            DB::transaction(function () use ($operation, $validated) {
                $operation->update([
                    'montant' => $validated['montant'] ?? $operation->montant,
                    'description' => $validated['description'] ?? $operation->description,
                    'date' => $validated['date'] ?? $operation->date,
                ]);
                // L'observer s'occupe du recalcul 
            });

            return response()->json([
                'message' => 'Opération mise à jour avec succès.',
                'operation' => $operation->fresh()->load('variable', 'sousVariable'), // Pour renvoyer les données mises à jour
            ]);
        } catch (\Throwable $e) {
            Log::error("Erreur lors de la mise à jour de l'opération : " . $e->getMessage());

            return response()->json([
                'error' => 'Une erreur est survenue lors de la mise à jour de l\'opération.',
            ], 500);
        }
    }
  

    // 4. destroy($id) — Supprimer une opération

    public function destroy($operationId)
    {
        $user = Auth::user();
        $operation = Operation::findOrFail($operationId);
        $variable = $operation->variable ?? $operation->sousVariable->variable;
        // dd($variable);
        if($variable->user_id !== $user->id) {
            return response()->json('Non autorisé', 401);
        }
        try {
            DB::transaction(function () use ($operation) {
                
                $operation->delete();
            });

            return response()->json(['message' => 'Opération supprimée avec succès.']);
         } catch (\Throwable $e) {
            Log::error("Erreur lors de la suppression de l'opération : " . $e->getMessage());

            return response()->json([
                'error' => 'Une erreur est survenue lors de la suppression de l\'opération.',
            ], 500);
        }
    }



}
