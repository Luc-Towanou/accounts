<?php

namespace Database\Factories;

use App\Models\SousVariable;
use App\Models\User;
use App\Models\Variable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Operation>
 */
class OperationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    // {
    //     return [
    //         //
    //         // 'user_id'               => User::factory(),
    //         'variable_id'           => Variable::factory(),
    //         'montant'               => $this->faker->randomFloat(2, 0, 2000),
    //         'description'           => $this->faker->randomFloat(2, 0, 2000), 
    //         'date'                  => $this->faker->dateTimeBetween('-1 months', '+1 months'),
    //         // 'statut_objet',      
    //         'sous_variable_id'      => SousVariable::factory(),
    //         'nature'                => $this->faker->randomElement(['sortie', 'entree'])
    //     ];
    // }
    {
        // Détermine si on lie à une sous-variable (true) ou à une variable (false)
        $useSousVar = $this->faker->boolean(50);

        return [
            'variable_id'      => $useSousVar
                                   ? null
                                   : Variable::factory(),
            'sous_variable_id' => $useSousVar
                                   ? SousVariable::factory()
                                   : null,
            'montant'          => $this->faker->randomFloat(2, 0, 2000),
            'description'      => $this->faker->sentence(),
            'date'             => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'nature'           => $this->faker->randomElement(['sortie', 'entree']),
        ];
    }
}
