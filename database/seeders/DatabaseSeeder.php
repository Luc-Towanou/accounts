<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            UserSeeder::class,
            // CategorieSeeder::class,
            MoisComptableFullV0::class,
            
            TestlucUserMoisSeeder::class,
            TestlucTableauxVariablesSeeder::class,
            TestlucOperationsSeeder::class,
            DefaultCategoriesSeeder::class,
            TestLucCategoriesSeeder::class,
        ]); //1|UDjHtfCrkAOZovYSaVawlXWI1NyNoARhtvkWjaird68a481a
    }
}
