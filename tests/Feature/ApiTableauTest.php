<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MoisComptable;
use App\Models\Tableau;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTableauTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_only_current_user_tableaux()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $moisA = MoisComptable::factory()->create(['user_id' => $userA->id]);
        $moisB = MoisComptable::factory()->create(['user_id' => $userB->id]);

        Tableau::factory()->for($moisA)->create(['nom' => 'A']);
        Tableau::factory()->for($moisB)->create(['nom' => 'B']);

        $response = $this->actingAs($userA)->getJson('/api/tableaux');

        $response->assertStatus(200)
                 ->assertJsonCount(1)
                 ->assertJsonFragment(['nom' => 'A'])
                 ->assertJsonMissing(['nom' => 'B']);
    }

    /** @test */
    public function store_creates_tableau_with_variables_and_sous_variables()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);

        $payload = [
            'mois_comptable_id' => $mois->id,
            'nom'               => 'My Tableau',
            'budget_prevu'      => 1000,
            'nature'            => 'entree',
            'variables'         => [
                [
                    'nom'          => 'VarSimple',
                    'type'         => 'simple',
                    'budget_prevu'=> 500,
                    'calcule'      => false,
                ],
                [
                    'nom'          => 'VarSousTableau',
                    'type'         => 'sous-tableau',
                    'budget_prevu'=> 300,
                    'sous_variables'=> [
                        [
                            'nom'          => 'SV1',
                            'budget_prevu'=> 100,
                            'calcule'      => false,
                        ],
                        [
                            'nom'          => 'SV2',
                            'budget_prevu'=> 200,
                            'calcule'      => false,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)
                         ->postJson('/api/tableaux', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nom' => 'My Tableau'])
                 ->assertJsonCount(2, 'variables')
                 ->assertJsonPath('variables.1.sous_variables.0.nom', 'SV1');

        $this->assertDatabaseHas('tableaux', [
            'nom'               => 'My Tableau',
            'mois_comptable_id' => $mois->id,
        ]);

        $this->assertDatabaseHas('variables', [
            'nom'        => 'VarSimple',
            'type'       => 'simple',
            'tableau_id' => $response->json('id'),
        ]);

        $this->assertDatabaseHas('sous_variables', [
            'nom'          => 'SV2',
            'variable_id'  => $response->json('variables')[1]['id'],
        ]);
    }

    /** @test */
    public function show_returns_single_tableau_with_relations()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tableau = Tableau::factory()->for($mois)->create();

        $response = $this->actingAs($user)->getJson("/api/tableaux/{$tableau->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $tableau->id]);
    }

    /** @test */
    public function update_modifies_tableau_fields()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tableau = Tableau::factory()->for($mois)->create(['nom' => 'OldName']);

        $response = $this->actingAs($user)
                         ->putJson("/api/tableaux/{$tableau->id}", ['nom' => 'NewName']);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'NewName']);

        $this->assertDatabaseHas('tableaux', ['id' => $tableau->id, 'nom' => 'NewName']);
    }

    /** @test */
    public function destroy_deletes_tableau()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tableau = Tableau::factory()->for($mois)->create();

        $response = $this->actingAs($user)->deleteJson("/api/tableaux/{$tableau->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tableaux', ['id' => $tableau->id]);
    }
}
