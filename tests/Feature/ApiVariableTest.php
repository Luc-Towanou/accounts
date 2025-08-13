<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MoisComptable;
use App\Models\Tableau;
use App\Models\Variable;
use App\Models\SousVariable;
use App\Models\RegleCalcul;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiVariableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_all_variables_for_authenticated_user()
    {
        $user = User::factory()->create();
        // Variables globales (pas de lien user)
        Variable::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/variables');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function index_by_tableau_returns_only_that_tableau_variables()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tableauA = Tableau::factory()->for($mois)->create();
        $tableauB = Tableau::factory()->for($mois)->create();

        Variable::factory()->for($tableauA)->count(2)->create();
        Variable::factory()->for($tableauB)->count(3)->create();

        $response = $this->actingAs($user)
                         ->getJson("/api/variables/tableau/{$tableauA->id}");

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    /** @test */
    public function store_creates_simple_variable()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tableau = Tableau::factory()->for($mois)->create();

        $payload = [
            'tableau_id'   => $tableau->id,
            'nom'          => 'TestVar',
            'type'         => 'simple',
            'budget_prevu'=> 150,
        ];

        $response = $this->actingAs($user)
                         ->postJson('/api/variables', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nom' => 'TestVar']);

        $this->assertDatabaseHas('variables', [
            'nom'        => 'TestVar',
            'tableau_id' => $tableau->id,
            'type'       => 'simple',
        ]);
    }

    /** @test */
    public function store_creates_sous_tableau_variable_and_sous_variables()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tableau = Tableau::factory()->for($mois)->create();

        $payload = [
            'tableau_id'     => $tableau->id,
            'nom'            => 'GroupVar',
            'type'           => 'sous-tableau',
            'sous_variables' => [
                ['nom' => 'ChildA', 'budget_prevu' => 50],
                ['nom' => 'ChildB', 'budget_prevu' => 80],
            ],
        ];

        $response = $this->actingAs($user)
                         ->postJson('/api/variables', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nom' => 'GroupVar'])
                 ->assertJsonCount(2, 'sous_variables');

        $this->assertDatabaseHas('sous_variables', [
            'nom'       => 'ChildB',
            'variable_id' => $response->json('id'),
        ]);
    }

    /** @test */
    public function show_returns_variable_with_relations_for_owner()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tableau = Tableau::factory()->for($mois)->create();
        $variable = Variable::factory()->for($tableau)->create();

        $response = $this->actingAs($user)
                         ->getJson("/api/variables/{$variable->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $variable->id]);
    }

    /** @test */
    public function update_modifies_variable_fields()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tableau = Tableau::factory()->for($mois)->create();
        $variable = Variable::factory()->for($tableau)->create(['nom' => 'OldName']);

        $response = $this->actingAs($user)
                         ->putJson("/api/variables/{$variable->id}", ['nom' => 'NewName']);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nom' => 'NewName']);

        $this->assertDatabaseHas('variables', [
            'id'   => $variable->id,
            'nom'  => 'NewName',
        ]);
    }

    /** @test */
    public function destroy_deletes_variable_if_not_used_in_rule()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tableau = Tableau::factory()->for($mois)->create();
        $variable = Variable::factory()->for($tableau)->create();

        $response = $this->actingAs($user)
                         ->deleteJson("/api/variables/{$variable->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('variables', ['id' => $variable->id]);
    }

    /** @test */
    public function destroy_fails_when_variable_used_in_regle_calcul()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tableau = Tableau::factory()->for($mois)->create();

        // Créer une sous-variable avec règle
        $variable = Variable::factory()->for($tableau)->create(['calcul' => true]);
        $sousVar = SousVariable::factory()->for($variable)->create();
        RegleCalcul::factory()->create([
            'variable_id' => $variable->id,
            'expression'  => "{$sousVar->nom}.{$sousVar->id} * 2",
        ]);

        $response = $this->actingAs($user)
                         ->deleteJson("/api/variables/{$variable->id}");

        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'Cette variable est déjà utilisée']);
    }
}
