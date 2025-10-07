<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class testCategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $user = \App\Models\User::where('id', 2)->firstOrFail();
        $categories = Categorie::where('mois_comptable_id', 12)->get();

        foreach ($categories as $categorie) {
            $categorie->mois_comptable_id = 8;
            $categorie->save();
        }
    }
}
