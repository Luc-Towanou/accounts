<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categorie>
 */
class CategorieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Générer un nom unique
        $nom = $this->faker->unique()->word();
        return [
            //
            'nom'          => ucfirst($nom),
            'slug'         => Str::slug($nom),
            'description'  => $this->faker->sentence(10),
            'icon'         => 'fa-' . $this->faker->randomElement([
                'coffee', 'book', 'cog', 'chart-line', 'heart'
            ]),
            'color'        => $this->faker->hexColor(),
            // 'statut_objet' => $this->faker->randomElement(['actif', 'inactif']),
        ];
    }
}
