<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
     $categories = [
    ['nom' => 'Transport',          'description' => 'Frais de déplacement en ville et longue distance',      'icon' => 'fa-bus',               'color' => '#1abc9c'],
    ['nom' => 'Logement',           'description' => 'Loyer, charges et assurances habitation',               'icon' => 'fa-home',              'color' => '#3498db'],
    ['nom' => 'Alimentation',       'description' => 'Courses, restaurants et cafés',                         'icon' => 'fa-utensils',          'color' => '#e67e22'],
    ['nom' => 'Éducation',          'description' => 'Frais de scolarité, matériel pédagogique',              'icon' => 'fa-graduation-cap',    'color' => '#9b59b6'],
    ['nom' => 'Loisirs',            'description' => 'Abonnements, sorties et activités de détente',          'icon' => 'fa-gamepad',           'color' => '#e74c3c'],
    ['nom' => 'Santé',              'description' => 'Frais médicaux, pharmacie et bien-être',                'icon' => 'fa-heartbeat',         'color' => '#27ae60'],
    ['nom' => 'Services',           'description' => 'Entretien, réparations et services professionnels',     'icon' => 'fa-concierge-bell',    'color' => '#8e44ad'],
    ['nom' => 'Vêtements',          'description' => 'Achats de vêtements et accessoires',                    'icon' => 'fa-tshirt',            'color' => '#d35400'],
    ['nom' => 'Assurances',         'description' => 'Primes d\'assurances auto, habitation, santé...',       'icon' => 'fa-shield-alt',        'color' => '#2c3e50'],
    ['nom' => 'Énergie',            'description' => 'Factures d\'électricité, gaz et chauffage',             'icon' => 'fa-bolt',              'color' => '#f1c40f'],
    ['nom' => 'Télécommunications', 'description' => 'Forfaits téléphonie mobile, internet et TV',            'icon' => 'fa-phone-volume',      'color' => '#16a085'],
    ['nom' => 'Impôts',             'description' => 'Taxes, impôts sur le revenu et cotisations sociales',   'icon' => 'fa-file-invoice-dollar','color' => '#c0392b'],
    ['nom' => 'Épargne',            'description' => 'Investissements, placements et économies',              'icon' => 'fa-piggy-bank',        'color' => '#2980b9'],
    ['nom' => 'Voyages',            'description' => 'Billets, hébergements et activités touristiques',       'icon' => 'fa-plane',             'color' => '#f39c12'],
    ['nom' => 'Autres',             'description' => 'Dépenses diverses non catégorisées',                    'icon' => 'fa-ellipsis-h',        'color' => '#7f8c8d'],
];

        foreach ($categories as $data) {
            Categorie::updateOrCreate(
                ['nom' => $data['nom']],
                array_merge($data, ['slug' => Str::slug($data['nom'])])
            );
        }
    }
}
