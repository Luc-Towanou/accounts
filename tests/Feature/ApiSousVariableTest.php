<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Variable;
use App\Models\SousVariable;
use App\Models\RegleCalcul;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiSousVariableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_all_sous_variables()
    {
        SousVariable::factory()->count(5)->create();

        $response = $this->getJson('/api/sous-variables');

        $response->assertStatus(200)
                 ->assertJsonCount(5);
    }

    /** @test */
    public function index_by_variable_returns_only_that_variable_sous_variables()
    {
        $variableA = Variable::factory()->create();
        $variableB = Variable::factory()->create();

        SousVariable::factory()->for($variableA)->count(3)->create();
        SousVariable::factory()->for($variableB)->count(2)->create();

        $response = $this->getJson("/api/variables/{$variableA->id}/sous-variables");

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function store_creates_a_sous_variable()
    {
        $variable = Variable::factory()->create();

        $payload = [
            'variable_id'  => $variable->id,
            'nom'          => 'SV Test',
            'budget_prevu' => 200,
            'calcule'      => false,
        ];

        $response = $this->postJson('/api/sous-variables', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nom' => 'SV Test']);

        $this->assertDatabaseHas('sous_variables', [
            'nom'         => 'SV Test',
            'variable_id' => $variable->id,
        ]);
    }

    /** @test */
    public function show_displays_the_sous_variable()
    {
        $sv = SousVariable::factory()->create();

        $response = $this->getJson("/api/sous-variables/{$sv->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $sv->id]);
    }

    /** @test */
    public function update_modifies_sous_variable_fields()
    {
        $sv = SousVariable::factory()->create(['nom' => 'AncienNom']);

        $payload = [
            'nom'          => 'NouveauNom',
            'budget_prevu' => 123.45,
            'variable_id'  => null,
        ];

        $response = $this->putJson("/api/sous-variables/{$sv->id}", $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'nom'          => 'NouveauNom',
                     'budget_prevu' => 123.45,
                 ]);

        $this->assertDatabaseHas('sous_variables', [
            'id'           => $sv->id,
            'nom'          => 'NouveauNom',
            'budget_prevu' => 123.45,
        ]);
    }

    /** @test */
    public function destroy_deletes_sous_variable()
    {
        $sv = SousVariable::factory()->create();

        $response = $this->deleteJson("/api/sous-variables/{$sv->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Sous-variable supprimée avec succès']);

        $this->assertDatabaseMissing('sous_variables', ['id' => $sv->id]);
    }

    /** @test */
    public function destroy_fails_if_sous_variable_used_in_a_regle_calcul()
    {
        $sv = SousVariable::factory()->create();
        $variable = $sv->variable;

        // Simuler une règle qui inclut cette sous-variable
        RegleCalcul::factory()->create([
            'variable_id' => $variable->id,
            'expression'  => "{$sv->nom}.{$sv->id} + 1",
        ]);

        $response = $this->deleteJson("/api/sous-variables/{$sv->id}");

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'message' => 'Cette sous-variable est déjà utilisée',
                 ]);
    }
}
