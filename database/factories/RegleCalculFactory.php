<?php

namespace Database\Factories;

use App\Models\RegleCalcul;
use App\Models\SousVariable;
use App\Models\User;
use App\Models\Variable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegleCalcul>
 */
class RegleCalculFactory extends Factory
{


    
           
    protected $model = RegleCalcul::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        // Création d’un utilisateur
        $user = User::factory()->create();

        // Génération d’un certain nombre aléatoire de "termes" (entre 2 et 5)
        $nbTermes = $this->faker->numberBetween(2, 5);

        $elements = [];
        for ($i = 0; $i < $nbTermes; $i++) {
            if ($this->faker->boolean) {
                // Créer une variable
                $var = Variable::factory()->create([
                    'user_id' => $user->id,
                ]);
                $elements[] = "{$var->nom}.{$var->id}";
            } else {
                // Créer une sous-variable
                $sousVar = SousVariable::factory()->create([
                    'user_id' => $user->id,
                ]);
                $elements[] = "{$sousVar->nom}.{$sousVar->id}";
            }

            // Ajouter un opérateur sauf après le dernier élément
            if ($i < $nbTermes - 1) {
                $elements[] = $this->faker->randomElement(['+', '-', '*', '/']);
            }
        }

        return [
            'user_id'          => $user->id,
            'variable_id'      => null,
            'sous_variable_id' => null,
            'expression'       => implode(' ', $elements),
            'statut_objet'     => 'actif',
        ];
    }

    /**
     * État pour forcer une règle sur une Variable existante
     */
    public function forVariable(Variable $variable)
    {
        // On crée un user
        $user = User::factory()->create();
        // Génération d’un certain nombre aléatoire de "termes" (entre 2 et 4)
        $nbTermes = $this->faker->numberBetween(2, 4);


        $elements = [];
        for ($i = 0; $i < $nbTermes; $i++) {
        
                // Créer une sous-variable
                $sousVar = SousVariable::factory()->create([
                    'user_id' => $user->id,
                ]);
                $elements[] = "{$sousVar->nom}.{$sousVar->id}";
            

            // Ajouter un opérateur sauf après le dernier élément
            if ($i < $nbTermes - 1) {
                $elements[] = $this->faker->randomElement(['+', '-', '*', '/']);
            }
        }

        return $this->state(function () use ($variable, $elements) {
            return [
                'user_id'          => $variable->user_id,
                'variable_id'      => $variable->id,
                'sous_variable_id' => null,
                'expression'       => implode(' ', $elements),
            ];
        });
    }



            // public function definition()
            // {
            //     // On crée un user
            //     $user = User::factory()->create();

            //     // On décide aléatoirement de lier la règle à une variable ou à une sous-variable
            //     if ($this->faker->boolean) {
            //         // Règle liée à une Variable
            //         $variable = Variable::factory()->create([
            //             'user_id' => $user->id,
            //         ]);

            //         return [
            //             'user_id'            => $user->id,
            //             'variable_id'        => $variable->id,
            //             'sous_variable_id'   => null,
            //             'expression'         => "{$variable->nom}.{$variable->id}",
            //             'statut_objet'       => 'actif',
            //         ];
            //     }

            //     // Règle liée à une SousVariable
            //     $sousVar = SousVariable::factory()->create([
            //         'user_id' => $user->id,
            //     ]);

            //     return [
            //         'user_id'            => $user->id,
            //         'variable_id'        => null,
            //         'sous_variable_id'   => $sousVar->id,
            //         'expression'         => "{$sousVar->nom}.{$sousVar->id}",
            //         'statut_objet'       => 'actif',
            //     ];
            // }

            /**
             * État pour forcer une règle de calcul sur une Variable existante
             */
            // public function forVariable(Variable $variable)
            // {
            //     return $this->state(function () use ($variable) {
            //         return [
            //             'user_id'          => $variable->user_id,
            //             'variable_id'      => $variable->id,
            //             'sous_variable_id' => null,
            //             'expression'       => "{$variable->nom}.{$variable->id}",
            //         ];
            //     });
            // }

            /**
            * État pour forcer une règle de calcul sur une SousVariable existante
            */
    public function forSousVariable(SousVariable $sousVar)
    {
                // On crée un user
                $user = User::factory()->create();
                // Génération d’un certain nombre aléatoire de "termes" (entre 2 et 4)
                $nbTermes = $this->faker->numberBetween(2, 4);


                $elements = [];
                for ($i = 0; $i < $nbTermes; $i++) {
                
                        // Créer une sous-variable
                        $sousVar = SousVariable::factory()->create([
                            'user_id' => $user->id,
                        ]);
                        $elements[] = "{$sousVar->nom}.{$sousVar->id}";
                    

                    // Ajouter un opérateur sauf après le dernier élément
                    if ($i < $nbTermes - 1) {
                        $elements[] = $this->faker->randomElement(['+', '-', '*', '/']);
                    }
                }   
                return $this->state(function () use ($sousVar, $elements) {
                    return [
                        'user_id'          => $sousVar->user_id,
                        'variable_id'      => null,
                        'sous_variable_id' => $sousVar->id,
                        'expression'       => implode(' ', $elements),
                    ];
                });
    }

}
