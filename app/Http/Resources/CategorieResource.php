<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategorieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

          // On charge les sous-catégories
        $enfants = $this->enfants()->get();

        // Détermination du champ hiérarchique à utiliser
        // (tableaux -> variables -> sous_variables)
        $childrenKey = match ($this->niveau) {
            1 => 'variables',
            2 => 'sous_variables',
            default => null,
        };
         // ---- Calcul du nombre total d'opérations ----
        $nombreOperations = match ($this->niveau) {
            3 => $this->operations()->count(), // sous-variable

            2 => $this->enfants()             // variable
                    ->withCount('operations')
                    ->get()
                    ->sum('operations_count'),

            1 => $this->enfants()             // tableau
                    ->with(['enfants' => fn($q) => $q->withCount('operations')])
                    ->get()
                    ->flatMap(fn($variable) => $variable->enfants)
                    ->sum('operations_count'),

            default => 0,
        };

        $data = [
            "id" => $this->id,
            "nom" => $this->nom,
            "niveau" => $this->niveau,
            "budget_prevu" => $this->budget_prevu,
            "depense_reelle" => $this->depense_reelle,
            "type"          => match($this->niveau) {
                                1 => 'tableau',
                                2 => 'variable',
                                3 => 'sous_variable',
                                default => 'exid_3',
                            },
             // 🔥 Ce que tu voulais ajouter :
            "nombre_operations" => $nombreOperations,
            // "gains_reelle" => $this->gains_reelle,
            // "montant_net" => $this->montant_net,
            "calcule" => $this->calcule,
            // "regle_calcul" => $this->regle_calcul,
            "statut_objet" => $this->statut_objet ?? 'actif',
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "enfants" => $enfants->count(),
        ];

        // Si la catégorie a des enfants, on les ajoute dans le bon champ
        if ($childrenKey && $enfants->isNotEmpty()) {
            $data[$childrenKey] = CategorieResource::collection($enfants);
        }

        return $data;
        // return parent::toArray($request);

        // // On détermine dynamiquement les sous-éléments (variables ou sous-variables)
        // $children = $this->children()->get();

        // return [
        //     "id" => $this->id,
        //     "nom" => $this->nom,
        //     "type" => $this->type,
        //     "budget_prevu" => $this->budget_prevu,
        //     "depense_reelle" => $this->depense_reelle,
        //     "calcule" => $this->calcule,
        //     "regle_calcul" => $this->regle_calcul,
        //     "created_at" => $this->created_at,
        //     "updated_at" => $this->updated_at,

        //     // s’il y a des sous-éléments, on les ajoute
        //     $this->when(
        //         $children->isNotEmpty(),
        //         fn() => [
        //             $this->niveau === 1 ? 2 : 3 
        //                 => CategorieResource::collection($children)
        //         ]
        //     ),
        // ];
    }
}
