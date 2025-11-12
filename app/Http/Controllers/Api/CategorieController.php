<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategorieResource;
use App\Models\Categorie;
use App\Models\MoisComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorieController extends Controller
{
    /**
     * Categories of user
     * @param \Illuminate\Http\Request $request
     */
    // public function index(Request $request)
    // {
    //     $user = Auth::user();
    //     // if ( $user ) return $user ->id;
    //     if ( !$user ) return response()->json('user null');
    //     // 🧭 On récupère le mois comptable actif
    //     $moisId = $request->query('mois_id') ?? $user->moisComptables()->latest()->first()?->id;

    //     if (!$moisId) {
    //         return response()->json(['message' => 'Aucun mois comptable trouvé.'], 404);
    //     }

    //     // 🎯 Catégories de l’utilisateur pour ce mois
    //     $categoriesUser = Categorie::where('mois_comptable_id', $moisId)
    //         ->where('user_id', $user->id)
    //         ->whereNull('parent_id')
    //         ->with('enfants')
    //         ->get();

    //     // 🌍 Catégories templates publiques (exclure celles déjà présentes chez l'utilisateur)
    //     $userCategoryNames = $categoriesUser->pluck('nom')->map(fn($n) => strtolower($n))->toArray();

    //     $categoriesTemplates = Categorie::where('is_template', true)
    //         ->where('visibilite', 'public')
    //         ->whereNull('parent_id')
    //         ->whereNotIn('nom', $userCategoryNames) // 🚫 évite les doublons
    //         ->with('enfants')
    //         ->get();

    //     // 🧩 Fusion des deux collections sans doublon
    //     $categories = $categoriesUser->merge($categoriesTemplates)->unique('nom')->values();

    //     // 🔁 Retour via la ressource
    //     return CategorieResource::collection($categories);
    // }
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json('user null');
        }

        // 🧭 On récupère le mois comptable actif
        $moisId = $request->query('mois_id') ?? $user->moisComptables()->latest()->first()?->id;

        if (!$moisId) {
            return response()->json(['message' => 'Aucun mois comptable trouvé.'], 404);
        }

        // 🎯 Catégories de l’utilisateur (racines avec enfants)
        $categoriesUser = Categorie::where('mois_comptable_id', $moisId)
            ->where('user_id', $user->id)
            ->whereNull('parent_id')
            ->whereHas('enfants') // ✅ ne garde que celles qui ont au moins un enfant
            ->with('enfants.enfants') // chargement récursif
            ->get();

        // 🌍 Catégories templates publiques (exclure celles déjà présentes chez l'utilisateur)
        $userCategoryNames = $categoriesUser->pluck('nom')->map(fn($n) => strtolower($n))->toArray();

        $categoriesTemplates = Categorie::where('is_template', true)
            ->where('visibilite', 'public')
            ->whereNull('parent_id')
            ->whereHas('enfants') // ✅ même filtrage pour les templates
            ->whereNotIn(DB::raw('LOWER(nom)'), $userCategoryNames) // évite les doublons insensibles à la casse
            ->with('enfants.enfants')
            ->get();

        // 🧩 Fusion sans doublon
        $categories = $categoriesUser->merge($categoriesTemplates)
            ->unique(fn($cat) => strtolower($cat->nom))
            ->values();

        // 🔁 Retour via la ressource
        return CategorieResource::collection($categories);
    }

   public function store(Request $request)
    {
        $validated = $request->validate([
            'mois_comptable_id' => 'nullable|exists:mois_comptables,id',
            'nom'               => 'required|string|max:255',
            'nature'            => 'nullable|in:entree,sortie',
            'budget_prevu'      => 'nullable|numeric',
            'parent_id'         => 'nullable|exists:categories,id',
            'niveau'            => 'required|in:1,2',
            'description'       => 'nullable|string',
            'is_template'       => 'boolean',
            'visibilite'        => 'in:public,prive',
        ]);

        $user = Auth::user();

        // 🔒 Vérifier cohérence entre niveau et parent_id
        if ($validated['niveau'] == 1 && !empty($validated['parent_id'])) {
            return response()->json(['message' => 'Une catégorie de niveau 1 ne peut pas avoir de parent.'], 422);
        }

        if ($validated['niveau'] == 2 && empty($validated['parent_id'])) {
            return response()->json(['message' => 'Une catégorie de niveau 2 doit avoir une catégorie parente.'], 422);
        }

        // 🔒 Vérifier appartenance du mois comptable à l’utilisateur si défini
        if (!empty($validated['mois_comptable_id'])) {
            $mois = MoisComptable::where('id', $validated['mois_comptable_id'])
                ->where('user_id', $user->id)
                ->first();

            if (!$mois) {
                return response()->json(['message' => 'Ce mois comptable ne vous appartient pas.'], 403);
            }
        }

        // 🔍 Vérifier doublon (même nom dans le même mois et même niveau)
        $exists = Categorie::where('nom', $validated['nom'])
            ->where('user_id', $user->id)
            ->when($validated['mois_comptable_id'] ?? null, fn($q) => 
                $q->where('mois_comptable_id', $validated['mois_comptable_id'])
            )
            ->when($validated['parent_id'] ?? null, fn($q) => 
                $q->where('parent_id', $validated['parent_id'])
            )
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Une catégorie portant ce nom existe déjà à ce niveau.'], 422);
        }

        try {
            DB::beginTransaction();

            $categorie = Categorie::create([
                'user_id'           => $user->id,
                'mois_comptable_id' => $validated['mois_comptable_id'] ?? null,
                'parent_id'         => $validated['parent_id'] ?? null,
                'nom'               => $validated['nom'],
                'niveau'            => $validated['niveau'],
                'description'       => $validated['description'] ?? null,
                'nature'            => $validated['nature'] ?? 'sortie',
                'statut_objet'      => 'actif',
                'budget_prevu'      => $validated['budget_prevu'] ?? null,
                'depense_reelle'    => 0,
                'calcule'           => false,
                'is_template'       => $validated['is_template'] ?? false,
                'visibilite'        => $validated['visibilite'] ?? 'prive',
                'date_debut'        => now(),
                'date_fin'          => null,
            ]);

            DB::commit();

            // Charger les sous-catégories si niveau 1
            if ($categorie->niveau == 1) {
                $categorie->load('sousCategories');
            }

            return response()->json([
                'message' => 'Catégorie créée avec succès',
                'data'    => $categorie
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création de la catégorie',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    //
    // public function index()
    // {
    //     return response()->json(Categorie::with('variables')->get());
    // }

    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'nom' => 'required|string|unique:categories,nom',
    //         'description' => 'nullable|string',
    //         'icon' => 'nullable|string',
    //         'color' => 'nullable|string',
    //     ]);

    //     $data['slug'] = Str::slug($data['nom']);

    //     $categorie = Categorie::create($data);

    //     return response()->json($categorie, 201);
    // }

    // public function show(Categorie $categorie)
    // {
    //     return response()->json($categorie->load('variables'));
    // }

    // public function update(Request $request, Categorie $categorie)
    // {
    //     $data = $request->validate([
    //         'nom' => 'required|string|unique:categories,nom,' . $categorie->id,
    //         'description' => 'nullable|string',
    //         'icon' => 'nullable|string',
    //         'color' => 'nullable|string',
    //     ]);

    //     $data['slug'] = Str::slug($data['nom']);

    //     $categorie->update($data);

    //     return response()->json($categorie);
    // }

    // public function destroy(Categorie $categorie)
    // {
    //     if ($categorie->variables()->exists()) {
    //         return response()->json([
    //             'error' => 'Impossible de supprimer : cette catégorie contient des variables.'
    //         ], 409);
    //     }

    //     $categorie->delete();
    //     return response()->json(['message' => 'Catégorie supprimée avec succès.']);
    // }


    // public function TotauxCategorie()
    // {
    //     $totauxParCategorie = Categorie::with(['variables.operations'])->get()->map(function ($categorie) {
    //     return [
    //         'categorie' => $categorie->nom,
    //         'total' => $categorie->variables->sum(function ($variable) {
    //             return $variable->operations->sum('montant'); // ou montant réel
    //         }),
    //     ];

    // });
    
    //     return response()->json($totauxParCategorie);
    // }


    // // Récupérer toutes les variables d'une catégorie donnée
    // public function variables($id)
    // {
    //     $categorie = Categorie::with('variables')->findOrFail($id);
    //     return response()->json($categorie->variables);
    // }

    // // Compter combien de variables par catégorie
    // public function countVariables()
    // {
    //     $data = Categorie::withCount('variables')->get();
    //     return response()->json($data);
    // }

    // // Chercher une catégorie par slug
    // public function bySlug($slug)
    // {
    //     $categorie = Categorie::where('slug', $slug)->with('variables')->firstOrFail();
    //     return response()->json($categorie);
    // }

    
}
