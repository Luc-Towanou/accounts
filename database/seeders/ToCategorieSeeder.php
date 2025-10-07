<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Tableau;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ToCategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::beginTransaction();

    try {
        $tableaux = Tableau::whereNotNull('user_id')->get();
        info('Nombre de tableaux : ' . $tableaux->count());

        $count = 0;

        foreach ($tableaux as $tableau) {
            info("Let's tab " . $tableau->nom);

            $TCategorie = Categorie::updateOrCreate([
                'user_id' => $tableau->user_id,
                'nom' => $tableau->nom,
                'mois_comptable_id' => $tableau->mois_comptable_id,
                'parent_id' => null,
                'niveau' => 1,
            ], [
                'description' => $tableau->description,
                'budget_prevu' => $tableau->budget_prevu,
                'nature' => $tableau->nature,
                'depense_reelle' => $tableau->depense_reelle,
                'is_template' => false,
                'date_debut' => $tableau->date_debut,
                'date_fin' => $tableau->date_fin,
            ]);

            $variables = $tableau->variables;
            info('Nombre de variables : ' . $variables->count());

            foreach ($variables as $variable) {
                info("Let's var " . $variable->nom);

                $VCategorie = Categorie::updateOrCreate([
                    'user_id' => $variable->user_id,
                    'nom' => $variable->nom,
                    'mois_comptable_id' => $tableau->mois_comptable_id,
                    'parent_id' => $TCategorie->id,
                    'niveau' => 2,
                ], [
                    'description' => $variable->description,
                    'budget_prevu' => $variable->budget_prevu,
                    'nature' => $TCategorie->nature,
                    'depense_reelle' => $variable->depense_reelle,
                    'is_template' => false,
                    'date_debut' => $variable->date_debut,
                    'date_fin' => $variable->date_fin,
                ]);

                $sousVariables = $variable->sousVariables;
                info('Nombre de sous-variables : ' . $sousVariables->count());

                foreach ($sousVariables as $sousVariable) {
                    info("Let's sousvar " . $sousVariable->nom);

                    Categorie::updateOrCreate([
                        'user_id' => $sousVariable->user_id,
                        'nom' => $sousVariable->nom,
                        'mois_comptable_id' => $tableau->mois_comptable_id,
                        'parent_id' => $VCategorie->id,
                        'niveau' => 3,
                    ], [
                        'description' => $sousVariable->description,
                        'budget_prevu' => $sousVariable->budget_prevu,
                        'nature' => $VCategorie->nature,
                        'depense_reelle' => $sousVariable->depense_reelle,
                        'is_template' => false,
                        'date_debut' => $sousVariable->date_debut,
                        'date_fin' => $sousVariable->date_fin,
                    ]);
                }
            }

            $count++;
            info('Nombre de tableaux traités : ' . $count);
        }

        DB::commit();
        info('Synchronisation terminée avec succès. Total : ' . $count);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Erreur lors de la synchronisation des tableaux : ' . $e->getMessage(), [
            'exception' => $e,
        ]);
        throw $e; // ou return response()->json(['error' => 'Une erreur est survenue'], 500);
    }
    //     $tableaux = Tableau::whereNotNull('user_id')->get();
    //     echo($tableaux->count() . '\n');
    //     $count = 0;
    //     foreach ($tableaux as $tableau ) {
    //         echo('Let\'s tab ' . $tableau->nom . '\n');
    //        $TCategorie = Categorie::updateOrCreate([
    //                 'user_id' => $tableau->user_id,
    //                 'nom' => $tableau->nom,
    //                 'mois_comptable_id' => $tableau->mois_comptable_id,
    //                 'parent_id' => null,
    //                 'niveau' => 1,
    //             ], [
    //                 'description' => $tableau->description,
    //                 'budget_prevu' =>$tableau->budget_prevu,
    //                 'nature' => $tableau->nature,
    //                 'depense_reelle' =>$tableau->depense_reelle,
    //                 'is_template' => false,
    //                 'date_debut' =>$tableau->date_debut,
    //                 'date_fin' =>$tableau->date_fin,
    //             ]); 
    //             $variables = $tableau->variables;
    //             echo($variables->count() . '\n');
    //             foreach ($variables as $variable ) {
    //                 echo('Let\'s var ' . $variable->nom . '\n');
    //             $VCategorie = Categorie::updateOrCreate([
    //                         'user_id' => $variable->user_id,
    //                         'nom' => $variable->nom,
    //                         'mois_comptable_id' => $tableau->mois_comptable_id,
    //                         'parent_id' => $TCategorie->id,
    //                         'niveau' => 2,
    //                     ], [
    //                         'description' => $variable->description,
    //                         'budget_prevu' =>$variable->budget_prevu,
    //                         'nature' => $TCategorie->nature,
    //                         'depense_reelle' =>$variable->depense_reelle,
    //                         'is_template' => false,
    //                         'date_debut' =>$variable->date_debut,
    //                         'date_fin' =>$variable->date_fin,
    //                     ]); 
    //                     $sousVariables = $variable->sousVariables;
    //                     echo($sousVariables->count() . '\n');
    //                     foreach ($sousVariables as $sousVariable ) {
    //                         echo('Let\'s sousvar ' . $sousVariable->nom . '\n');
    //                     $SousVCategorie = Categorie::updateOrCreate([
    //                                 'user_id' => $sousVariable->user_id,
    //                                 'nom' => $sousVariable->nom,
    //                                 'mois_comptable_id' => $tableau->mois_comptable_id,
    //                                 'parent_id' => $VCategorie->id,
    //                                 'niveau' => 3,
    //                             ], [
    //                                 'description' => $sousVariable->description,
    //                                 'budget_prevu' =>$sousVariable->budget_prevu,
    //                                 'nature' => $VCategorie->nature,
    //                                 'depense_reelle' =>$sousVariable->depense_reelle,
    //                                 'is_template' => false,
    //                                 'date_debut' =>$sousVariable->date_debut,
    //                                 'date_fin' =>$sousVariable->date_fin,
    //                             ]); 
    //                     }
    //             }
    //         $count ++ ;
    //         echo('nombre tab ' . $count . '\n');
    //     }
    //     echo('Complete ' . $count . '\n');
    }
}

