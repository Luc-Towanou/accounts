<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestLucCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

          $now = Carbon::now();

        $categories = [
            'Vie quotidienne' => [
                'Hygiène personnelle', 'Produits ménagers', 'Services domestiques',
                'Fournitures diverses', 'Animaux de compagnie', 'Cadeaux et dons',
                'Abonnements', 'Divers imprévus'
            ],
            'Santé' => [
                'Consultation médicale', 'Pharmacie / médicaments', 'Hospitalisation',
                'Assurance santé', 'Soins dentaires', 'Optique',
                'Analyses et examens', 'Bien-être'
            ],
            'Nourriture' => [
                'Courses alimentaires', 'Restaurants', 'Cantine / déjeuner au travail',
                'Boissons', 'Café / thé', 'Snacks / fast-food',
                'Produits bio / diététiques', 'Livraison de repas'
            ],
            'Logement' => [
                'Loyer / crédit immobilier', 'Électricité', 'Eau', 'Gaz',
                'Entretien / réparations', 'Assurance habitation',
                'Meubles / décoration', 'Taxe foncière ou locative'
            ],
            'Vêtement' => [
                'Vêtements quotidiens', 'Chaussures', 'Accessoires',
                'Entretien (pressing, couture)', 'Sous-vêtements',
                'Tenues professionnelles', 'Tenues de sport', 'Tenues de cérémonie'
            ],
            'Transport' => [
                'Carburant', 'Transport en commun', 'Assurance véhicule',
                'Entretien / réparation', 'Stationnement / péage',
                'Achat de véhicule', 'Taxi / VTC', 'Vélo / trottinette / transport alternatif'
            ],
            'Éducation' => [
                'Frais de scolarité', 'Fournitures scolaires', 'Livres / documentation',
                'Formation professionnelle', 'Cours particuliers',
                'Abonnement éducatif en ligne', 'Uniformes', 'Activités parascolaires'
            ],
            'Loisir' => [
                'Cinéma / spectacles', 'Voyages / vacances', 'Sport / club',
                'Sorties (bars, concerts, etc.)', 'Jeux vidéo',
                'Matériel de loisir', 'Événements / fêtes', 'Loisirs créatifs'
            ],
            'Épargne' => [
                'Épargne de précaution', 'Épargne projet', 'Épargne logement',
                'Épargne retraite', 'Compte bloqué / livret',
                'Tontine / groupement d’épargne', 'Dépôt fixe', 'Remboursement de dettes'
            ],
            'Investissements' => [
                'Actions / bourse', 'Immobilier locatif', 'Crypto-monnaies',
                'Business personnel', 'Prêt à un tiers', 'Investissement agricole',
                'Fonds / obligations', 'Startups ou participations'
            ],
            'Communication' => [
                'Forfait téléphonique', 'Internet', 'Achat de téléphone',
                'Réparation / accessoires', 'Abonnement TV / streaming',
                'Applications payantes', 'Frais postaux', 'Abonnement cloud'
            ],
            'Charges' => [
                'Impôts et taxes', 'Cotisations sociales', 'Assurance (autre)',
                'Frais bancaires', 'Frais administratifs', 'Honoraires',
                'Pensions / aides familiales', 'Dettes / remboursements de prêts'
            ],
        ];
        $user = \App\Models\User::where('id', 2)->firstOrFail();

        foreach ($categories as $parentName => $subNames) {
            // Insertion de la catégorie principale
            $parentId = DB::table('categories')->insertGetId([
                'mois_comptable_id' => 12,
                'user_id' => $user->id,
                'nom' => $parentName,
                'niveau' => 1,
                'nature' => 'sortie',
                'statut_objet' => 'actif',
                'is_template' => true,
                'visibilite' => 'public',
                'calcule' => false,
                'budget_prevu' => null,
                'depense_reelle' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Insertion des sous-catégories
            foreach ($subNames as $subName) {
                DB::table('categories')->insert([
                    'nom' => $subName,
                    'mois_comptable_id' => 12,
                    'user_id' => $user->id,
                    'niveau' => 2,
                    'parent_id' => $parentId,
                    'nature' => 'sortie',
                    'statut_objet' => 'actif',
                    'is_template' => true,
                    'visibilite' => 'public',
                    'calcule' => false,
                    'budget_prevu' => null,
                    'depense_reelle' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    
    }
}
