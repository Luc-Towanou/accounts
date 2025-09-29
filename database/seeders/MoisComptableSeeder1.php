<?php

namespace Database\Seeders;

use App\Models\MoisComptable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MoisComptableSeeder1 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MoisComptable::create([
            'user_id' => 1, 
            'mois' => 'septembre',
            'annee' => 2025,
            'statut_objet' => 'actif',
            'date_debut' => '2025-01-01',
            'date_fin' => '2025-01-31',
            'budget_prevu' => 200000,
            'depense_reelle' => 150000,
            'gains_reelle' => 50000,
            'montant_net' => (50000 - 150000), // -100000
        ]);
    }
}
