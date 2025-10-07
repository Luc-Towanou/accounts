<?php

namespace Database\Seeders;

use App\Models\Operation;
use App\Models\SousVariable;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestlucOperationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pour testluc@gmail.com
        // $user = \App\Models\User::where('email', 'luctest@gmail.com')->first();
        $user = \App\Models\User::where('id', 2)->first();

        foreach (Variable::where('type', 'simple')->where('user_id', $user->id)->get() as $variable) {
            if ($variable->regleCalcul) {
              
                $tableau = $variable->tableau;
                $dateDebut = Carbon::create(2025, 5, 1)->startOfDay(); // tu peux ajuster
                $dateFin   = $dateDebut->copy()->endOfMonth()->endOfDay();

                for ($k = 1; $k <= rand(1, 2); $k++) {
                    $dateTime = fake()->dateTimeBetween($dateDebut, $dateFin);
                    Operation::create([
                        'variable_id' => $variable->id,
                        'montant' => rand(1000, 5000),
                        'description' => fake()->sentence(3),
                        'date' => $dateTime->format('Y-m-d H:i:s'),
                        'nature' => $tableau->nature,
                    ]); 
                }
              
            }
        }

        foreach (SousVariable::whereNotNull('variable_id')->where('user_id', $user->id)->get() as $sv) {
            $tableau = $sv->variable->tableau;
            $dateDebut = Carbon::create(2025, 5, 1)->startOfDay();
            $dateFin   = $dateDebut->copy()->endOfMonth()->endOfDay();

            for ($k = 1; $k <= rand(1, 3); $k++) {
                $dateTime = fake()->dateTimeBetween($dateDebut, $dateFin);
                Operation::create([
                    'sous_variable_id' => $sv->id,
                    'montant' => rand(1000, 5000),
                    'description' => fake()->sentence(3),
                    'date' => $dateTime->format('Y-m-d H:i:s'),
                    'nature' => $tableau->nature,
                ]);
            }
        }

    }
}
