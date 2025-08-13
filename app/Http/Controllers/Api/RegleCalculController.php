<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoisComptable;
use App\Models\RegleCalcul;
use App\Models\SousVariable;
use App\Models\Tableau;
use App\Models\Variable;
use App\Services\RegleCalculService;
use Exception;
use Illuminate\Http\Request;

class RegleCalculController extends Controller
{
    //

    // Liste des règles
   public function index()
{
    $user = auth()->user();

    // 1) On récupère le mois le plus récent de l’utilisateur
    $mois = MoisComptable::where('user_id', $user->id)
            ->orderBy('date_debut', 'desc')
            ->first();

    // En cas d’absence de mois, on renvoie un 404 explicite
    if (! $mois) {
        return response()->json(['regles' => []], 200);
    }

    // 2) On récupère toutes les règles liées aux variables de ce mois
    $regles = RegleCalcul::with('variable')
        ->whereHas('variable.tableau', function($q) use ($mois) {
            $q->where('mois_comptable_id', $mois->id);
        })
        ->get();

    // 3) On renvoie le JSON
    return response()->json([
        'regles' => $regles
    ], 200);
}


    // Détails d'une règle
    public function show($id)
    {
        $user = auth()->user();
        $regle = RegleCalcul::findOrFail($id);


        $variable = Variable::findOrFail($regle->id);

        $tableau = Tableau::findOrFail($variable->tableau_id);

        
        $mois = MoisComptable::where('user_id', $user->id)
                             ->where('id', $tableau->mois_comptable_id)
                             ->first();

        
        if (!$mois) {
            abort(401, 'Non autorisé');
        }
        $regle = RegleCalcul::with('variable')->findOrFail($id);
        return response()->json($regle);
    }

    // Création
    public function store(Request $request, RegleCalculService $validator)
    {
        $validated = $request->validate([
            'variable_id' => 'required|exists:variables,id',
            'expression' => 'required|string',
        ]);
        $variable = Variable::findOrFail($validated['variable_id']);
        
        $user = auth()->user();

        $tableau = Tableau::findOrFail($variable->tableau_id);

        
        $mois = MoisComptable::where('user_id', $user->id)
                             ->where('id', $tableau->mois_comptable_id)
                             ->first();
        
        if (!$mois) {
            abort(401, 'Non autorisé');
        }

         try {
                $validator->validerExpression($validated['expression']);
                 $regle = $variable->regleCalcul()->create([
                    'expression' => $validated['expression'],
                ]);
            } catch (Exception $e) {
                return response()->json(['erreur' => $e->getMessage()], 422);
            }
        // $regle = RegleCalcul::create($validated);

        return response()->json(['message' => 'Règle créée avec succès.', 'regle' => $regle], 201);
    }

    // Mise à jour
    public function update(Request $request, $id, RegleCalculService $validator)
    {
        $user = auth()->user();
        $regle = RegleCalcul::findOrFail($id);


        $variable = Variable::findOrFail($regle->id);

        $tableau = Tableau::findOrFail($variable->tableau_id);

        
        $mois = MoisComptable::where('user_id', $user->id)
                             ->where('id', $tableau->mois_comptable_id)
                             ->first();

        
        if (!$mois) {
            abort(401, 'Non autorisé');
        }

        $validated = $request->validate([
            'expression' => 'required|string',
        ]);
        try {
                $validator->validerExpression($validated['expression']);
                
                $regle->update($validated);

            } catch (Exception $e) {
                return response()->json(['erreur' => $e->getMessage()], 422);
            }

        $regle->update($validated);

        return response()->json(['message' => 'Règle mise à jour.', 'regle' => $regle]);
    }

    // Suppression
    public function destroy($id)
    {
        $user = auth()->user();
        $regle = RegleCalcul::findOrFail($id);


        $variable = Variable::findOrFail($regle->id);

        $tableau = Tableau::findOrFail($variable->tableau_id);

        
        $mois = MoisComptable::where('user_id', $user->id)
                             ->where('id', $tableau->mois_comptable_id)
                             ->first();

        
        if (!$mois) {
            abort(401, 'Non autorisé');
        }

        
        $regle->delete();

        return response()->json(['message' => 'Règle supprimée.']);
    }



    //fonctions  de test

    protected $service;

    public function __construct(RegleCalculService $service)
    {
        $this->service = $service;
    }

    // 1. Tester l’évaluation d’une expression
    public function evaluer(Request $request)
    {
        $request->validate(['expression' => 'required|string']);
        $resultat = $this->service->evaluer($request->expression);
        return response()->json(['resultat' => $resultat]);
    }

    public function evaluerVariable (Request $request, $variableId)
    {
        $variable = Variable::findOrFail($variableId);
        $expression = $variable->regleCalcul->expression;
        $resultat = $this->service->evaluer($expression);
        return response()->json(['resultat' => $resultat]);
    }

    public function analyseRegle($id )
{
    $regle = RegleCalcul::with('variable.tableau')->findOrFail($id);
    try {
        $resultat = $this->service->analyser($regle);
        return response()->json($resultat);
    } catch (\Exception $e) {
        return response()->json(['erreur' => $e->getMessage()], 422);
    }
}


    // 2. Tester la validation d’une expression
    public function valider(Request $request)
    {
        $request->validate(['expression' => 'required|string']);
        try {
            $this->service->validerExpression($request->expression);
            return response()->json(['message' => 'Expression valide']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // 3. Vérifier si une variable est utilisée dans une règle de calcul
    public function variableRegle($id)
    {
        $variable = Variable::findOrFail($id);
        $variableParent = $this->service->variableRegleCalcul($variable);

        return response()->json([
            'variable_utilisee_par' => $variableParent ? "{$variableParent->tableau->nom}.{$variableParent->nom}" : null,
        ]);
    }

    // 4. Vérifier si une sous-variable est utilisée dans une règle de calcul
    public function sousVariableRegle($id)
    {
        $sousVariable = SousVariable::findOrFail($id);
        $variableParent = $this->service->sousVariableRegleCalcul($sousVariable);

        return response()->json([
            'sous_variable_utilisee_par' => $variableParent ? "{$variableParent->tableau->nom}.{$variableParent->nom}" : null,
        ]);
    }
}
