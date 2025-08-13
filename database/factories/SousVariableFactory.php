<?php

namespace Database\Factories;

use App\Models\Categorie;
use App\Models\User;
use App\Models\Variable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SousVariable>
 */
class SousVariableFactory extends Factory
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
            'variable_id'           => Variable::factory(),
            'nom'                   => $this->faker->word,
            'budget_prevu'          => $this->faker->randomFloat(2, 0, 2000),
            'categorie_id'          => Categorie::factory(),
            'depense_reelle'        => $this->faker->randomFloat(2, 0, 2000),
            // 'regle_calcul',
            // 'statut_objet',
            'calcule'               => $this->faker->boolean(30),
        ];
    }
}
