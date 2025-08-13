<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\MoisComptable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoisComptableTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_only_user_mois()
    {
        $user = User::factory()->create();
        MoisComptable::factory()->count(2)->create(['user_id' => $user->id]);
        MoisComptable::factory()->count(3)->create(); // autres users

        $this->actingAs($user)
            ->getJson('/api/mois-comptables')
            ->assertOk()
            ->assertJsonCount(2, 'mois');
    }

    public function test_store_creates_mois_with_tableaux_and_variables()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api_token')->plainTextToken;

        $payload = [
            'mois' => 'Janvier',
            'annee' => 2025,
            'tableaux' => [
                [
                    'nom' => 'Recettes',
                    'nature' => 'entree',
                    'variables' => [
                        [
                            'nom' => 'Ventes',
                            'type' => 'simple',
                            'calcule' => false
                        ]
                    ]
                ]
            ]
        ];

        $this->withHeader('Authorization', "Bearer {$token}")
            ->actingAs($user)
            ->postJson('/api/mois-comptables', $payload)
            ->assertCreated()
            ->assertJson(['message' => 'Mois comptable créé avec succès 🎉']);

        $this->assertDatabaseHas('mois_comptables', [
            'mois' => 'Janvier',
            'annee' => 2025,
            'user_id' => $user->id
        ]);

        $this->assertDatabaseHas('tableaux', [
            'nom' => 'Recettes'
        ]);

        $this->assertDatabaseHas('variables', [
            'nom' => 'Ventes'
        ]);
    }

    public function test_store_prevents_duplicate_month()
    {
        $user = User::factory()->create();
        MoisComptable::factory()->create([
            'mois' => 'Février',
            'annee' => 2025,
            'user_id' => $user->id
        ]);

        $payload = [
            'mois' => 'Février',
            'annee' => 2025
        ];

        $this->actingAs($user)
            ->postJson('/api/mois-comptables', $payload)
            ->assertStatus(409)
            ->assertJson(['message' => 'Un mois comptable portant ce nom existe déjà']);
    }
}
