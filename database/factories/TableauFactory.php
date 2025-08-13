<?php

namespace Database\Factories;

use App\Models\MoisComptable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tableau>
 */
class TableauFactory extends Factory
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
            'user_id'                   => User::factory(),
            'mois_comptable_id'         => MoisComptable::factory(),
            'nom'                       => $this->faker->word, 
            'budget_prevu'              => $this->faker->randomFloat(2, 0, 2000),
            // 'statut_objet', 
            'date_debut'                => $this->faker->date('Y-m-d'),
            'date_fin'                  => $this->faker->date('Y-m-d'),
            'description'               => $this->faker->sentence,
            'depense_reelle'            => $this->faker->randomFloat(2, 0, 2000),
            'nature'                    => $this->faker->randomElement(['sortie', 'entree'])
        ];
    }
}
