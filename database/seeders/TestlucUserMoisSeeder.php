<?php

namespace Database\Seeders;

use App\Models\MoisComptable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestlucUserMoisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // $user = User::updateOrCreate([
        //     'name' => 'TEST',
        //     'prenom' => 'Luc',
        //     'email' => 'luctest@gmail.com',
        // ], [
        //     'password' => bcrypt('password'),
        // ]);

        for ($moisId = 5; $moisId < 9; $moisId++) {
            $nomMois = match ($moisId) {
                5 => 'mai',
                6 => 'juin',
                7 => 'juillet',
                8 => 'aout',
            };

            $dateDebut = Carbon::create(2025, $moisId, 1)->startOfDay();
            $dateFin   = $dateDebut->copy()->endOfMonth()->endOfDay();

            MoisComptable::updateOrCreate([
                'user_id' => 2,
                'mois' => $nomMois,
                'annee' => 2025,
            ], [
                'date_debut'   => $dateDebut,
                'date_fin'     => $dateFin,
                'budget_prevu' => rand(200000, 750000),
            ]);
        }
    }
}
