<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MoisComptableRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
         return [
            "mois" => [
                "id" => $this->id,
                "user_id" => $this->user_id,
                "mois" => $this->mois,
                "annee" => $this->annee,
                "statut_objet" => $this->statut_objet,
                "date_debut" => $this->date_debut,
                "date_fin" => $this->date_fin,
                "budget_prevu" => $this->budget_prevu,
                "depense_reelle" => $this->depense_reelle,
                // "gains_reelle" => $this->gains_reelle,
                // "montant_net" => $this->montant_net,
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at,
                "tableaux" => CategorieResource::collection(
                    $this->categories()
                        ->whereNull('parent_id')
                        ->where('niveau', 1)
                        ->get()
                ),
            ],
        ];
    }
}
