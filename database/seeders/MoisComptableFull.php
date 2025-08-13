<?php

namespace Database\Seeders;

use App\Models\MoisComptable;
use App\Models\Operation;
use App\Models\RegleCalcul;
use App\Models\SousTableau;
use App\Models\SousVariable;
use App\Models\Tableau;
use App\Models\User;
use App\Models\Variable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoisComptableFull extends Seeder
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

            //  Mois comptable : Mai 2025
            $mois = MoisComptable::updateOrCreate([
                'user_id' => $user->id,
                'mois' => 'Mai',
                'annee' => 2025,
            ]);

        // Quelques noms d'exemples de tableaux
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
                'nom' => $nomTableau,
                'budget_prevu' => rand(50000, 150000),
                'mois_comptable_id' => $mois->id, // à ajuster selon tes données
            ]);

            // 4 à 6 variables par tableau
            for ($i = 1; $i <= rand(4, 6); $i++) {
                $nomVariable = 'Variable_' . $i;
                $isCalculee = $i <= 2; // Les deux premières seulement sont calculées

                $variable = Variable::updateOrCreate([
                    'tableau_id' => $tableau->id,
                    'nom' => $nomVariable, 
                    'budget_prevu' => rand(10000, 30000),
                    'calcule' => $isCalculee,
                ]);

                if ($isCalculee) {
                    //creation de sous-variable sans variables parente :
                    for ($i = 1; $i <= rand(2, 3); $i++) {
                        $nomSousVariable = 'SousVariable_' . $i;
                        $isCalculee = $i <= 2; // Les deux premières seulement sont calculées

                        $sousVariable = SousVariable::updateOrCreate([
                            // 'tableau_id' => $tableau->id,
                            'nom' => $nomSousVariable,
                            'budget_prevu' => rand(5000, 7000),
                            'categorie_id' => rand(1, 10),
                            // 'calcule' => $isCalculee,
                        ]);
                    }
                    // Création d'une règle de calcul avec des sous-variables d'autres variables du même tableau
                    $autresSousVariables = SousVariable::whereNull('variable_id')
                                                        ->inRandomOrder()
                                                        ->limit(random_int(2, 3))
                                                        ->get();
                    // $autresSousVariables = SousVariable::whereHas('variable', function ($q) use ($tableau) {
                    //     $q->where('tableau_id', $tableau->id)->where('calcule', false);
                    // })->inRandomOrder()->limit(rand(2, 3))->get();

                    // Si pas assez de sous-variables existantes, on skip
                    if ($autresSousVariables->count() < 2) continue;

                    $expression = $autresSousVariables->map(function ($sv) use ($variable) {
                        return $variable->tableau->nom . '.' . $variable->nom . '.' . $sv->nom;
                    })->implode(' + ');

                    RegleCalcul::updateOrCreate([
                        'expression' => $expression,
                        'variable_id' => $variable->id,
                    ]);
                } else {
                    // 1 à 3 sous-variables pour les non calculées
                    for ($j = 1; $j <= rand(1, 3); $j++) {
                        $nomSV = 'SV_' . $j;

                        $sousVariable = SousVariable::updateOrCreate([
                            'variable_id' => $variable->id,
                            'nom' => $nomSV,
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


        // DB::transaction(function () {

        //     // Création utilisateur test
        //     $user = User::first() ?? User::factory()->create([
        //         'name' => 'TEST',
        //         'prenom' => 'Luc',
        //         'email' => 'luctest@gmail.com',
        //         'password' => bcrypt('password'),
        //     ]);

        //     //  Mois comptable : Mai 2025
        //     $mois = MoisComptable::create([
        //         'user_id' => $user->id,
        //         'mois' => 'Mai',
        //         'annee' => 2025,
        //     ]);

        //     // === TABLEAU : Enfant_Ange ===
        //     $tableau1 = $mois->tableaux()->create([
        //         'nom' => 'Enfant_Ange',
        //         'budget_prevu' => 120000,
        //     ]);

        //     // Variable simple : Scolarité
        //     $scolarite = $tableau1->variables()->create([
        //         'nom' => 'Scolarité',
        //         'type' => 'simple',
        //         'calcule' => false,
        //         'budget_prevu' => 60000,
        //     ]);

        //     $scolarite->operations()->create([
        //         'montant' => 60000,
        //         'description' => 'Paiement scolarité mai',
        //         'date' => '2025-05-10',
        //     ]);

        //     // 🔹 Sous-tableau : CoursBasket_Ang
        //     $sousTableau1 = $tableau1->sousTableaux()->create([
        //         'nom' => 'CoursBasket_Ang',
        //         'budget_prevu' => 40000,
        //     ]);

        //     // ➤ Variables simples dans le sous-tableau
        //     $deplacement = $sousTableau1->variables()->create([
        //         'nom' => 'Déplacement',
        //         'type' => 'simple',
        //         'calcule' => false,
        //         'budget_prevu' => 10000,
        //     ]);

        //     $gouter = $sousTableau1->variables()->create([
        //         'nom' => 'Goûter',
        //         'type' => 'simple',
        //         'calcule' => false,
        //         'budget_prevu' => 5000,
        //     ]);

        //     $paiement = $sousTableau1->variables()->create([
        //         'nom' => 'Paiement_Tranche',
        //         'type' => 'simple',
        //         'calcule' => false,
        //         'budget_prevu' => 20000,
        //     ]);

        //     // ➤ Ajout des opérations
        //     foreach ([
        //         [$deplacement, 5000, 'Taxi matin', '2025-05-10'],
        //         [$deplacement, 6000, 'Bus retour', '2025-05-31'],
        //         [$gouter, 2000, 'Goûter mardi', '2025-05-10'],
        //         [$gouter, 2500, 'Goûter jeudi', '2025-05-31'],
        //         [$paiement, 20000, 'Tranche unique', '2025-05-10'],
        //     ] as [$variable, $montant, $desc, $date]) {
        //         $variable->operations()->create([
        //             'montant' => $montant,
        //             'description' => $desc,
        //             'date' => $date,
        //         ]);
        //     }

        //     // Variable calculee (résultat)
        //     $coursBasket = $tableau1->variables()->create([
        //         'nom' => 'CoursBasket',
        //         'type' => 'simple',
        //         'calcule' => true,
        //         'budget_prevu' => 40000,
        //     ]);

        //     $coursBasket->regleCalcul()->create([
        //         'expression' => 'variable:Déplacement + variable:Goûter + variable:Paiement_Tranche',
        //     ]);
            
        //                 // === TABLEAU : Maison ===
        //     $tableau2 = $mois->tableaux()->create([
        //         'nom' => 'Maison',
        //         'budget_prevu' => 150000,
        //     ]);

        //     // 🔹 Variables simples non calculées
        //     $electricite = $tableau2->variables()->create([
        //         'nom' => 'Electricite',
        //         'type' => 'simple',
        //         'calculé' => false,
        //         'budget_prevu' => 6000,
        //     ]);
        //     $electricite->operations()->create([
        //         'montant' => 6000,
        //         'description' => 'Paiement électricité avril',
        //         'date' => '2025-05-20',
        //     ]);

        //     $eau = $tableau2->variables()->create([
        //         'nom' => 'Eau',
        //         'type' => 'simple',
        //         'calculé' => false,
        //         'budget_prevu' => 8000,
        //     ]);
        //     $eau->operations()->create([
        //         'montant' => 8000,
        //         'description' => 'Paiement eau avril',
        //         'date' => '2025-05-19',
        //     ]);

        //     $impot = $tableau2->variables()->create([
        //         'nom' => 'Impot',
        //         'type' => 'simple',
        //         'calculé' => false,
        //         'budget_prevu' => 6000,
        //     ]);
        //     $impot->operations()->create([
        //         'montant' => 6000,
        //         'description' => 'Paiement impôt mai',
        //         'date' => '2025-05-19',
        //     ]);

        //     $wifi = $tableau2->variables()->create([
        //         'nom' => 'Wifi',
        //         'type' => 'simple',
        //         'calculé' => false,
        //         'budget_prevu' => 20000,
        //     ]);
        //     $wifi->operations()->create([
        //         'montant' => 20000,
        //         'description' => 'Paiement wifi juin',
        //         'date' => '2025-05-24',
        //     ]);

        //     $abonnement_chaines = $tableau2->variables()->create([
        //         'nom' => 'Abonnement_chaines',
        //         'type' => 'simple',
        //         'calculé' => false,
        //         'budget_prevu' => 20000,
        //     ]);
        //     $abonnement_chaines->operations()->create([
        //         'montant' => 20000,
        //         'description' => 'Paiement abonnement chaînes juin',
        //         'date' => '2025-05-24',
        //     ]);

        //     $loyer = $tableau2->variables()->create([
        //         'nom' => 'Loyer',
        //         'type' => 'simple',
        //         'calculé' => false,
        //         'budget_prevu' => 60000,
        //     ]);
        //     $loyer->operations()->create([
        //         'montant' => 60000,
        //         'description' => 'Paiement loyer mai',
        //         'date' => '2025-05-05',
        //     ]);

        //     $gaz = $tableau2->variables()->create([
        //         'nom' => 'Gaz',
        //         'type' => 'simple',
        //         'calculé' => false,
        //         'budget_prevu' => 10000,
        //     ]);
        //     $gaz->operations()->create([
        //         'montant' => 10000,
        //         'description' => 'Paiement gaz mai',
        //         'date' => '2025-05-05',
        //     ]);

        //     // === Sous-tableau : Lessive_Maison ===
        //     $st_lessive = $tableau2->sousTableaux()->create([
        //         'nom' => 'Lessive_Maison',
        //         'budget_prevu' => 10000,
        //     ]);

        //     $lessive_tr = $st_lessive->variables()->create([
        //         'nom' => 'Lessive_tr1',
        //         'type' => 'simple',
        //         'calculé' => false,
        //         'budget_prevu' => 10000,
        //     ]);

        //     foreach ([
        //         [$lessive_tr, 2000, 'Lessive_tr1', '2025-05-10'],
        //         [$lessive_tr, 2000, 'Lessive_tr2', '2025-05-17'],
        //         [$lessive_tr, 2000, 'Lessive_tr3', '2025-05-24'],
        //         [$lessive_tr, 2500, 'Lessive_tr4', '2025-05-31'],
        //     ] as [$variable, $montant, $desc, $date]) {
        //         $variable->operations()->create([
        //             'montant' => $montant,
        //             'description' => $desc,
        //             'date' => $date,
        //         ]);
        //     }

        //     // === Sous-tableau : Emplettes_Maison ===
        //     $st_emplettes = $tableau2->sousTableaux()->create([
        //         'nom' => 'Emplettes_Maison',
        //         'budget_prevu' => 50000,
        //     ]);

        //     $super_marche = $st_emplettes->variables()->create([
        //         'nom' => 'Super_Marche',
        //         'type' => 'simple',
        //         'calculé' => false,
        //         'budget_prevu' => 20000,
        //     ]);

        //     foreach ([
        //         [$super_marche, 18000, 'Emplette Supermarché', '2025-05-02'],
        //         [$super_marche, 7000, 'Achat Rasoir', '2025-05-16'],
        //     ] as [$variable, $montant, $desc, $date]) {
        //         $variable->operations()->create([
        //             'montant' => $montant,
        //             'description' => $desc,
        //             'date' => $date,
        //         ]);
        //     }

        //     $marche = $st_emplettes->variables()->create([
        //         'nom' => 'Marché',
        //         'type' => 'simple',
        //         'calculé' => false,
        //         'budget_prevu' => 20000,
        //     ]);
        //     $marche->operations()->create([
        //         'montant' => 18000,
        //         'description' => 'Marché Mai',
        //         'date' => '2025-05-01',
        //     ]);

        //     $achat_quartier = $st_emplettes->variables()->create([
        //         'nom' => 'Emplettes_tr',
        //         'type' => 'simple',
        //         'calculé' => false,
        //         'budget_prevu' => 10000,
        //     ]);

        //     foreach ([
        //         [500, 'Achat Sachet', '2025-05-01'],
        //         [300, 'Achat Glace', '2025-05-08'],
        //         [800, 'Achat Lampe', '2025-05-10'],
        //         [1000, 'Achat Tirage', '2025-05-17'],
        //         [600, 'Achat Lampe', '2025-05-19'],
        //         [150, 'Achat Glace', '2025-05-24'],
        //         [1500, 'Achat Bière', '2025-05-25'],
        //         [700, 'Achat Bissap', '2025-05-30'],
        //         [800, 'Achat Tirage', '2025-05-31'],
        //     ] as [$montant, $desc, $date]) {
        //         $achat_quartier->operations()->create([
        //             'montant' => $montant,
        //             'description' => $desc,
        //             'date' => $date,
        //         ]);
        //     }

        //     // 🔹 Variable calculée : Emplette
        //     $emplette = $tableau2->variables()->create([
        //         'nom' => 'Emplette',
        //         'type' => 'simple',
        //         'calculé' => true,
        //         'budget_prevu' => 50000,
        //     ]);

        //     $emplette->regleCalcul()->create([
        //         'expression' => 'variable:Super_Marche + variable:Marché + variable:Emplettes_tr',
        //     ]);


        // });

    //   DB::transaction(function () {
            
       
        //     // Créer un utilisateur de test
        //     $user = User::first() ?? User::factory()->create([
        //         'name' => 'TOWANOU',
        //         'prenom' => 'Luc',
        //         'email' => 'luctowanou@gmail.com',
        //         'password' => bcrypt('password'),
        //     ]);

        //     // Mois comptable : Juillet 2025
        //     $mois = MoisComptable::create([
        //         'user_id' => $user->id,
        //         'mois' => 'Mai',
        //         'annee' => 2025,
        //     ]);

        //     // === Tableau 1 : Enfant_Ange ===
        //     $tableau1 = $mois->tableaux()->create([
        //         'nom' => 'Enfant_Ange',
        //         'budget_prevu' => 120000,
        //     ]);

        //     // Variables fixes
        //     $scolarite = $tableau1->variables()->create([
        //         'nom' => 'Scolarité',
        //         'type' => 'fixe',
        //         'budget_prevu' => 60000,
        //         // 'depense_reelle' => 60000,
        //     ]);

        //     $scolarite->operations()->create([
        //         'variable_id' => $scolarite->id,
        //         'montant' => 60000,
        //         'description' => 'Paiement scolarité mai',
        //         'date' => '2025-05-10',
        //     ]);

        //     // Sous-tableau : CoursBasket_Ang
        //     $sousTableau1 = $tableau1->sousTableaux()->create([
        //         'nom' => 'CoursBasket_Ang',
        //         'budget_prevu' => 40000,
        //     ]);

        //     // Variables du sous-tableau
        //     $deplacement = $sousTableau1->variables()->create([
        //         'nom' => 'Déplacement',
        //         'type' => 'fixe',
        //         'budget_prevu' => 10000,
        //         'depense_reelle' => 11000,
        //     ]);

        //     $gouter = $sousTableau1->variables()->create([
        //         'nom' => 'Goûter',
        //         'type' => 'fixe',
        //         'budget_prevu' => 5000,
        //         'depense_reelle' => 4500,
        //     ]);

        //     $paiement = $sousTableau1->variables()->create([
        //         'nom' => 'Paiement_Tranche',
        //         'type' => 'fixe',
        //         'budget_prevu' => 20000,
        //         'depense_reelle' => 20000,
        //     ]);

        //     // Ajout d'opérations pour chaque variable
        //     foreach ([
        //         [$deplacement, 5000, 'Taxi matin', '2025-05-10'],
        //         [$deplacement, 6000, 'Bus retour', '2025-05-31'],
        //         [$gouter, 2000, 'Goûter mardi', '2025-05-10'],
        //         [$gouter, 2500, 'Goûter jeudi', '2025-05-31'],
        //         [$paiement, 20000, 'Tranche unique', '2025-05-10'],
        //     ] as [$variable, $montant, $desc, $date]) {
        //         $variable->operations()->create([
        //             'montant' => $montant,
        //             'description' => $desc,
        //             'date' => $date,
        //         ]);   
        //     }

        //     // Variable résultat liée aux 3 précédentes
        //     $coursBasket = $tableau1->variables()->create([
        //         'nom' => 'CoursBasket',
        //         'type' => 'resultat',
        //         'budget_prevu' => 40000,
        //         'depense_reelle' => 35500, // total des opérations des 3
        //     ]);

        //     // Règle de calcul
        //     $coursBasket->regleCalcul()->create([
        //         'expression' => 'variable:Déplacement + variable:Goûter + variable:Paiement_Tranche',
        //     ]);

        //     // === Tableau 2 : Maison ===
        //     $tableau2 = $mois->tableaux()->create([
        //         'nom' => 'Maison',
        //         'budget_prevu' => 150000,
        //     ]);

        //     // Variables fixes
        //     $electricite = $tableau2->variables()->create([
        //         'nom' => 'Electricite',
        //         'type' => 'fixe',
        //         'budget_prevu' => 6000,
        //     ]);
        //     $electricite->operations()->create([
        //         'variable_id' => $electricite->id,
        //         'montant' => 6000,
        //         'description' => 'Paiement electricite avril',
        //         'date' => '2025-05-20',
        //     ]);
        //     //
        //     $eau = $tableau2->variables()->create([
        //         'nom' => 'Eau',
        //         'type' => 'fixe',
        //         'budget_prevu' => 8000,
        //         'depense_reelle' => 8000,
        //     ]);
        //     $eau->operations()->create([
        //         'variable_id' => $eau->id,
        //         'montant' => 8000,
        //         'description' => 'Paiement eau avril',
        //         'date' => '2025-05-19',
        //     ]);
        //     //
        //     $impot = $tableau2->variables()->create([
        //         'nom' => 'Impot',
        //         'type' => 'fixe',
        //         'budget_prevu' => 6000,
        //         'depense_reelle' => 6000,
        //     ]);
        //     $impot->operations()->create([
        //         'variable_id' => $impot->id,
        //         'montant' => 6000,
        //         'description' => 'Paiement impot mai',
        //         'date' => '2025-05-19',
        //     ]);
        //     //
        //     $wifi = $tableau2->variables()->create([
        //         'nom' => 'Wifi',
        //         'type' => 'fixe',
        //         'budget_prevu' => 20000,
        //         'depense_reelle' => 20000,
        //     ]);
        //     $wifi->operations()->create([
        //         'variable_id' => $wifi->id,
        //         'montant' => 20000,
        //         'description' => 'Paiement wifi juin',
        //         'date' => '2025-05-24',
        //     ]);
        //     //
            
        //     $abonnement_chaines = $tableau2->variables()->create([
        //         'nom' => 'Abonnement_chaines',
        //         'type' => 'fixe',
        //         'budget_prevu' => 20000,
        //         'depense_reelle' => 20000,
        //     ]);
        //     $abonnement_chaines->operations()->create([
        //         'variable_id' => $abonnement_chaines->id,
        //         'montant' => 20000,
        //         'description' => 'Paiement de l\'abonnement chaines juin',
        //         'date' => '2025-05-24',
        //     ]);
        //     //

        //     $loyer = $tableau2->variables()->create([
        //         'nom' => 'Loyer',
        //         'type' => 'fixe',
        //         'budget_prevu' => 60000,
        //         'depense_reelle' => 60000,
        //     ]);
        //     $loyer->operations()->create([
        //         'variable_id' => $loyer->id,
        //         'montant' => 60000,
        //         'description' => 'Paiement loyer mai',
        //         'date' => '2025-05-05',
        //     ]);
        //     //

        //     $gaz = $tableau2->variables()->create([
        //         'nom' => 'Gaz',
        //         'type' => 'fixe',
        //         'budget_prevu' => 10000,
        //         'depense_reelle' => 10000,
        //     ]);
        //     $gaz->operations()->create([
        //         'variable_id' => $gaz->id,
        //         'montant' => 10000,
        //         'description' => 'Paiement gaz mai',
        //         'date' => '2025-05-05',
        //     ]);
        //     //

        //     //variable du tableau non fixes  (sous-tableau)
        //     $st_lessive = $tableau2->sousTableaux()->create([
        //         'nom' => 'Lessive_Maison',
        //         'budget_prevu' => 10000,
        //     ]);

        //     // Variable du sous-tableau lessive
        //     $lessive_tr = $st_lessive->variables()->create([
        //         'nom' => 'Lessive_tr1',
        //         'type' => 'resultat',
        //         'budget_prevu' => 10000,
        //         // 'depense_reelle' => 10000,
        //     ]);
        //     // Ajout d'opérations pour la variable lessive_tr
        //     foreach ([
        //         [$lessive_tr, 2000, 'Lessive_tr1', '2025-05-10'],
        //         [$lessive_tr, 2000, 'Lessive_tr2', '2025-05-17'],
        //         [$lessive_tr, 2000, 'Lessive_tr3', '2025-05-24'],
        //         [$lessive_tr, 2500, 'Lessive_tr4', '2025-05-31'], 
        //     ] as [$variable, $montant, $desc, $date]) {
        //         $variable->operations()->create([
        //             'montant' => $montant,
        //             'description' => $desc,
        //             'date' => $date,
        //         ]);
        //     }

        //     $st_emplettes = $tableau2->sousTableaux()->create([
        //         'nom' => 'Emplettes_Maison',
        //         'budget_prevu' => 50000,
        //     ]);

            

        //     // variables du sous-tableau emplettes
        //     $super_marche = $st_emplettes->variables()->create([
        //         'nom' => 'Super_Marche',
        //         'type' => 'resultat',
        //         'budget_prevu' => 20000,
        //     ]);

        //     foreach ([
        //         [$super_marche, 18000, 'Emplette Supermarché', '2025-05-02'],
        //         [$super_marche, 7000, 'Achat Rasoire', '2025-05-16'],
        //     ] as [$variable, $montant, $desc, $date]) {
        //         $variable->operations()->create([
        //             'montant' => $montant,
        //             'description' => $desc,
        //             'date' => $date,
        //         ]);
        //     }

        //     $marché = $st_emplettes->variables()->create([
        //         'nom' => 'Marché',
        //         'type' => 'resultat',
        //         'budget_prevu' => 20000,
        //     ]);

        //     $marché->operations()->create([
        //         'variable_id' => $marché->id,
        //         'montant' => 18000,
        //         'description' => 'Marché Mai',
        //         'date' => '2025-05-01',
        //     ]);


        //     $achat_quartier = $st_emplettes->variables()->create([
        //         'nom' => 'Emplettes_tr',
        //         'type' => 'resultat',
        //         'budget_prevu' => 10000,
        //     ]);

        //     // Ajout d'opérations pour la variable lessive_tr
        //     foreach ([
        //         [$achat_quartier, 500, 'Achat Sachet', '2025-05-01'],
        //         [$achat_quartier, 300, 'Achat Glace', '2025-05-08'],
        //         [$achat_quartier, 800, 'Achat Lampe', '2025-05-10'],
        //         [$achat_quartier, 1000, 'Achat Tirage', '2025-05-17'],
        //         [$achat_quartier, 600, 'Achat Lampe', '2025-05-19'], 
        //         [$achat_quartier, 150, 'Achat glace', '2025-05-24'], 
        //         [$achat_quartier, 1500, 'Achat Beer', '2025-05-25'], 
        //         [$achat_quartier, 700, 'Achat Bissap', '2025-05-30'], 
        //         [$achat_quartier, 800, 'Achat Tirage', '2025-05-31'], 
    
        //     ] as [$variable, $montant, $desc, $date]) {
        //         $variable->operations()->create([
        //             'montant' => $montant,
        //             'description' => $desc,
        //             'date' => $date,
        //         ]);
        //     }

        //     // Variable résultat liée aux 3 précédentes
        //     $emplette = $tableau2->variables()->create([
        //         'nom' => 'Emplette',
        //         'type' => 'resultat',
        //         'budget_prevu' => 50000,
        //         // 'depense_reelle' => 35500, // total des opérations des 3
        //     ]);

        //     // Règle de calcul
        //     $emplette->regleCalcul()->create([
        //         'expression' => 'variable:Super_Marche + variable:Marché + variable:Emplettes_tr',
        //     ]);

            
        //     // tb maison credit immobilier, gaz, charbon, electricité, eau, impo, wifi, soins, habonnement_chaînes, entretient/reparation, répétiteur, transports_scollaire, quantite-enfant, epargne
        //     // femme variable vetement tresses, maquilage, mensualité 100.000, soins_beauté, 
        //     });
//     }
     
// }

