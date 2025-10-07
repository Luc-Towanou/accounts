<?php

namespace Database\Seeders;


use App\Models\MoisComptable;
use App\Models\Operation;
use App\Models\RegleCalcul;
use App\Models\SousVariable;
use App\Models\Tableau;
use App\Models\User;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Nette\Utils\Random;

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

            for ($moisId = 5; $moisId < 9; $moisId++) {

                if ($moisId === 5) $nomMois = 'Mai';
                if ($moisId === 6) $nomMois = 'Juin';
                if ($moisId === 7) $nomMois = 'Juillet';
                if ($moisId === 8) $nomMois = 'Aout';

                $dateDebut = Carbon::create(2025, $moisId, 1)->startOfDay();
                $dateFin   = $dateDebut->copy()->endOfMonth()->endOfDay();

            // Mois comptable : Mai - Aout 2025
            $mois = MoisComptable::updateOrCreate([
                'user_id' => $user->id,
                'mois' => $nomMois,
                'annee' => 2025,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'budget_prevu' => rand(200000, 750000),
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
                    'description' => 'Test',
                    'budget_prevu' => rand(50000, 150000),
                    'mois_comptable_id' => $mois->id,
                    'nature' => fake()->randomElement(['sortie', 'entree']),
                ]);

                // 4 à 6 variables par tableau
                for ($i = 1; $i <= rand(4, 6); $i++) {
                    $nomVariable = 'Variable_' . $nomTableau . $i;
                    $isCalculee = $i <= 2; // les deux premières seulement sont calculées

                    $variable = Variable::updateOrCreate([
                        'user_id' => $user->id,
                        'tableau_id' => $tableau->id,
                        // 'categorie_id' => rand(1, 10),
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
                                // 'categorie_id' => rand(1, 10),
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

                        // Création de la règle de calcul
                        RegleCalcul::updateOrCreate([
                            'user_id' => $user->id,
                            'variable_id' => $variable->id,
                        ], [
                            'expression' => $expression,
                        ]);
                    } else {
                        // Variables non calculées 
                        $variable ->type = 'sous-tableau';
                        $variable ->save();
                        // → sous-variables liées directement
                        for ($j = 1; $j <= rand(1, 3); $j++) {
                            $sousVariable = SousVariable::updateOrCreate([
                                'user_id' => $user->id,
                                'variable_id' => $variable->id,
                                'nom' => 'SV_' . $j,
                                'budget_prevu' => rand(2000, 8000),
                                // 'categorie_id' => rand(1, 10),
                            ]);

                            // 1 à 3 opérations par sous-variable
                            // Bornes jour et nuit
                            $dayStart   = $dateDebut->copy()->setTime(8, 0, 0);
                            $dayEnd     = $dateFin->copy()->setTime(22, 0, 0);
                            $nightStart = $dateDebut->copy()->startOfDay();
                            $nightEnd   = $dateDebut->copy()->setTime(8, 0, 0);
                            $lateStart  = $dateFin->copy()->setTime(22, 0, 0);
                            $lateEnd    = $dateFin->copy()->endOfDay();

                            for ($k = 1; $k <= rand(1, 3); $k++) {
                                // Pondération : 90% jour, 10% nuit
                                if (rand(1, 100) <= 90) {
                                    $dateTime = fake()->dateTimeBetween($dayStart, $dayEnd);
                                } else {
                                    // Choix aléatoire entre tôt le matin ou tard le soir
                                    if (rand(0, 1)) {
                                        $dateTime = fake()->dateTimeBetween($nightStart, $nightEnd);
                                    } else {
                                        $dateTime = fake()->dateTimeBetween($lateStart, $lateEnd);
                                    }
                                }
                                Operation::create([
                                    'sous_variable_id' => $sousVariable->id,
                                    'montant' => rand(1000, 5000),
                                    'description' => fake()->sentence(3),
                                    'date' => $dateTime->format('Y-m-d H:i:s'),
                                    'nature' => $tableau->nature,
                                ]);
                            }
                        }
                    }   
                }
                // 1 varisable simple non calculée
                    $variableSimple = Variable::updateOrCreate([
                        'user_id' => $user->id,
                        'tableau_id' => $tableau->id,
                        'nom' => 'Variable_simple' . $nomTableau,
                        'type' => 'simple',
                        'budget_prevu' => rand(10000, 30000),
                    ]);

                // 1 à 2 opérations par variable simple
                    // Bornes jour et nuit
                    $dayStart   = $dateDebut->copy()->setTime(8, 0, 0);
                    $dayEnd     = $dateFin->copy()->setTime(22, 0, 0);
                    $nightStart = $dateDebut->copy()->startOfDay();
                    $nightEnd   = $dateDebut->copy()->setTime(8, 0, 0);
                    $lateStart  = $dateFin->copy()->setTime(22, 0, 0);
                    $lateEnd    = $dateFin->copy()->endOfDay();

                    for ($k = 1; $k <= rand(1, 2); $k++) {
                        // Pondération : 90% jour, 10% nuit
                        if (rand(1, 100) <= 90) {
                            $dateTime = fake()->dateTimeBetween($dayStart, $dayEnd);
                        } else {
                            // Choix aléatoire entre tôt le matin ou tard le soir
                            if (rand(0, 1)) {
                                $dateTime = fake()->dateTimeBetween($nightStart, $nightEnd);
                            } else {
                                $dateTime = fake()->dateTimeBetween($lateStart, $lateEnd);
                            }
                        }

                        Operation::create([
                            'variable_id' => $variableSimple->id,
                            'montant'     => rand(1000, 5000),
                            'description' => fake()->sentence(3),
                            'date'        => $dateTime->format('Y-m-d H:i:s'),
                            'nature'      => $tableau->nature,
                        ]);
                    }
            }
            }
        });

    }
}
