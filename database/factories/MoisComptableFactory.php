<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MoisComptable>
 */
class MoisComptableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
                'user_id'            => User::factory(),
                'mois'               => $this->faker->monthName,
                'annee'              => $this->faker->year,
                // 'statut_objet',
                'budget_prevu'       => $this->faker->randomFloat(2, 0, 5000), 
                'date_debut'         => $this->faker->date('Y-m-d'),
                'date_fin'           => $this->faker->date('Y-m-d'),
                // 'depense_reelle',
                // 'gains_reelle',
                // 'montant_net',
        ];
    }
}

