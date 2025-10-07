<?php

namespace Database\Seeders;

use App\Models\MoisComptable;
use App\Models\RegleCalcul;
use App\Models\SousVariable;
use App\Models\Tableau;
use App\Models\Variable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestlucTableauxVariablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pour testluc@gmail.com
        // $user = \App\Models\User::where('email', 'luctest@gmail.com')->first();
        $user = \App\Models\User::where('id', 2)->firstOrFail();

        $tableauxNoms = ['Vie Quotidienne', 'Loisirs', 'Santé', 'Éducation', 'Transports', 'Investissements'];

        foreach (MoisComptable::where('user_id', $user->id)->get() as $mois) {
            foreach ($tableauxNoms as $nomTableau) {
                $tableau = Tableau::updateOrCreate([
                    'user_id' => $user->id,
                    'nom' => $nomTableau,
                    'mois_comptable_id' => $mois->id,
                ], [
                    'description' => 'TestTab1',
                    'budget_prevu' => rand(50000, 150000),
                    'nature' => fake()->randomElement(['sortie', 'entree']),
                ]);

                // Variables
                for ($i = 1; $i <= rand(4, 6); $i++) {
                    $isCalculee = $i <= 2;
                    $variable = Variable::updateOrCreate([
                        'user_id' => $user->id,
                        'tableau_id' => $tableau->id,
                        'nom' => 'Variable_' . $nomTableau . $i,
                    ], [
                        'categorie_id' => rand(1, 10),
                        'budget_prevu' => rand(10000, 30000),
                        'calcule' => $isCalculee,
                    ]);

                    if ($isCalculee) {
                        // sous-variables libres
                        for ($svIndex = 1; $svIndex <= rand(2, 3); $svIndex++) {
                            SousVariable::updateOrCreate([
                                'user_id' => $user->id,
                                'nom' => 'SousVariable_' . $svIndex,
                                'budget_prevu' => rand(5000, 7000),
                                'categorie_id' => rand(1, 10),
                                'variable_id' => null,
                            ]);
                        }
                    } else {
                        $variable->type = 'sous-tableau';
                        $variable->save();

                        for ($j = 1; $j <= rand(1, 3); $j++) {
                            SousVariable::updateOrCreate([
                                'user_id' => $user->id,
                                'variable_id' => $variable->id,
                                'nom' => 'SV_' . $j,
                                'budget_prevu' => rand(2000, 8000),
                                'categorie_id' => rand(1, 10),
                            ]);
                        }
                    }
                }

                // Variable simple
                Variable::updateOrCreate([
                    'user_id' => $user->id,
                    'tableau_id' => $tableau->id,
                    'nom' => 'Variable_simple' . $nomTableau,
                ], [
                    'type' => 'simple',
                    'budget_prevu' => rand(10000, 30000),
                ]);
            }
        }

        // Règles de calcul après coup pour variables calcule de l'utilisateur
        foreach (Variable::where('calcule', true)->where('user_id', $user->id)->get() as $variable) {
            $autresSV = SousVariable::whereNull('variable_id')->inRandomOrder()->limit(rand(2, 3))->get();
            if ($autresSV->count() >= 2) {
                $expression = $autresSV->map(fn($sv) => "{$sv->nom}.{$sv->id}")->implode(' + ');
                RegleCalcul::updateOrCreate([
                    'user_id' => $variable->user_id,
                    'variable_id' => $variable->id,
                ], [
                    'expression' => $expression,
                ]);
            }
        }
    }
}
