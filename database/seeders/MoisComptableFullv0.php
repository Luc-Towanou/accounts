<?php

namespace Database\Seeders;


use App\Models\MoisComptable;
use App\Models\Operation;
use App\Models\RegleCalcul;
use App\Models\SousVariable;
use App\Models\Tableau;
use App\Models\User;
use App\Models\Variable;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MoisComptableFullv0 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::transaction(function () {
            // Création utilisateur test
            $user = User::updateOrCreate([
                'name' => 'TEST',
                'prenom' => 'Luc',
                'email' => 'luctest@gmail.com',
                'password' => bcrypt('password'),
            ]);

            // Mois comptable : Mai 2025
            $mois = MoisComptable::updateOrCreate([
                'user_id' => $user->id,
                'mois' => 'Mai',
                'annee' => 2025,
            ]);

            // Exemples de tableaux
            $tableaux = [
                'Vie Quotidienne',
                'Loisirs',
                'Santé',
                'Éducation',
                'Transports',
                'Investissements',
            ];

            foreach ($tableaux as $nomTableau) {
                $tableau = Tableau::updateOrCreate([
                    'user_id' => $user->id,
                    'nom' => $nomTableau,
                    'budget_prevu' => rand(50000, 150000),
                    'mois_comptable_id' => $mois->id,
                ]);

                // 4 à 6 variables par tableau
                for ($i = 1; $i <= rand(4, 6); $i++) {
                    $nomVariable = 'Variable_' . $i;
                    $isCalculee = $i <= 2; // les deux premières seulement sont calculées

                    $variable = Variable::updateOrCreate([
                        'user_id' => $user->id,
                        'tableau_id' => $tableau->id,
                        'nom' => $nomVariable,
                        'budget_prevu' => rand(10000, 30000),
                        'calcule' => $isCalculee,
                    ]);

                    if ($isCalculee) {
                        // Création de sous-variables libres (sans variable_id)
                        for ($svIndex = 1; $svIndex <= rand(2, 3); $svIndex++) {
                            SousVariable::updateOrCreate([
                                'user_id' => $user->id,
                                'nom' => 'SousVariable_' . $svIndex,
                                'budget_prevu' => rand(5000, 7000),
                                'categorie_id' => rand(1, 10),
                                'variable_id' => null, // reste libre
                            ]);
                        }

                        // Sélection aléatoire de sous-variables libres pour la règle
                        $autresSousVariables = SousVariable::whereNull('variable_id')
                            ->inRandomOrder()
                            ->limit(random_int(2, 3))
                            ->get();

                        if ($autresSousVariables->count() < 2) {
                            continue;
                        }

                        // Nouveau format : NomSousVariable.id
                        $expression = $autresSousVariables
                            ->map(fn($sv) => "{$sv->nom}.{$sv->id}")
                            ->implode(' + ');

                        RegleCalcul::updateOrCreate([
                            'user_id' => $user->id,
                            'variable_id' => $variable->id,
                        ], [
                            'expression' => $expression,
                        ]);
                    } else {
                        // Variables non calculées → sous-variables liées directement
                        for ($j = 1; $j <= rand(1, 3); $j++) {
                            $sousVariable = SousVariable::updateOrCreate([
                                'user_id' => $user->id,
                                'variable_id' => $variable->id,
                                'nom' => 'SV_' . $j,
                                'budget_prevu' => rand(2000, 8000),
                                'calcule' => false,
                            ]);

                            // 1 à 3 opérations par sous-variable
                            for ($k = 1; $k <= rand(1, 3); $k++) {
                                Operation::create([
                                    'sous_variable_id' => $sousVariable->id,
                                    'montant' => rand(1000, 5000),
                                    'description' => fake()->sentence(3),
                                    'date' => now()->subDays(rand(1, 30)),
                                ]);
                            }
                        }
                    }
                }
            }
        });

    }
}
