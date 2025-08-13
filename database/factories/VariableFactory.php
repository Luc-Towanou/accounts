<?php

namespace Database\Factories;

use App\Models\Categorie;
use App\Models\Tableau;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Variable>
 */
class VariableFactory extends Factory
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
            'user_id'               => User::factory(),
            'tableau_id'            => Tableau::factory(),
            'nom'                   => $this->faker->word, 
            'type'                  => $this->faker->randomElement(['simple', 'sous-tableau']), 
            'budget_prevu'          => $this->faker->randomFloat(2, 0, 2000),
            'depense_reelle'        => $this->faker->randomFloat(2, 0, 2000),
            // 'statut_objet'          ,
            'calcule'               => $this->faker->boolean(30),
            'categorie_id'           => Categorie::factory(),
        ];
    }
}
