<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategorieController extends Controller
{
    //
    public function index()
    {
        return response()->json(Categorie::with('variables')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|unique:categories,nom',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        $data['slug'] = Str::slug($data['nom']);

        $categorie = Categorie::create($data);

        return response()->json($categorie, 201);
    }

    public function show(Categorie $categorie)
    {
        return response()->json($categorie->load('variables'));
    }

    public function update(Request $request, Categorie $categorie)
    {
        $data = $request->validate([
            'nom' => 'required|string|unique:categories,nom,' . $categorie->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        $data['slug'] = Str::slug($data['nom']);

        $categorie->update($data);

        return response()->json($categorie);
    }

    public function destroy(Categorie $categorie)
    {
        if ($categorie->variables()->exists()) {
            return response()->json([
                'error' => 'Impossible de supprimer : cette catégorie contient des variables.'
            ], 409);
        }

        $categorie->delete();
        return response()->json(['message' => 'Catégorie supprimée avec succès.']);
    }


    public function TotauxCategorie()
    {
        $totauxParCategorie = Categorie::with(['variables.operations'])->get()->map(function ($categorie) {
        return [
            'categorie' => $categorie->nom,
            'total' => $categorie->variables->sum(function ($variable) {
                return $variable->operations->sum('montant'); // ou montant réel
            }),
        ];

    });
    
        return response()->json($totauxParCategorie);
    }


    // Récupérer toutes les variables d'une catégorie donnée
    public function variables($id)
    {
        $categorie = Categorie::with('variables')->findOrFail($id);
        return response()->json($categorie->variables);
    }

    // Compter combien de variables par catégorie
    public function countVariables()
    {
        $data = Categorie::withCount('variables')->get();
        return response()->json($data);
    }

    // Chercher une catégorie par slug
    public function bySlug($slug)
    {
        $categorie = Categorie::where('slug', $slug)->with('variables')->firstOrFail();
        return response()->json($categorie);
    }

    
}
