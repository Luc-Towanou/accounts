<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MoisComptable;
use App\Models\Tableau;
use App\Models\Variable;
use App\Models\SousVariable;
use App\Models\RegleCalcul;
use App\Services\RegleCalculService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiRegleCalculTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_rules_for_latest_month()
    {
        $user = User::factory()->create();

        // Ancien mois – ne doit pas être sélectionné
        MoisComptable::factory()->create([
            'user_id'    => $user->id,
            'date_debut' => '2025-01-01',
        ]);

        // Mois le plus récent pour cet utilisateur
        $latest = MoisComptable::factory()->create([
            'user_id'    => $user->id,
            'date_debut' => '2025-03-01',
        ]);

        $tableau  = Tableau::factory()->for($latest)->create();
        $variable = Variable::factory()->for($tableau)->create();
        $regle     = RegleCalcul::factory()->forVariable($variable)->create();

        $response = $this->actingAs($user)
                         ->getJson('/api/regles-calcul');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'regles')
                 ->assertJsonFragment(['id' => $regle->id]);
    }

    /** @test */
    public function show_denies_access_when_rule_not_in_user_month()
    {
        $user    = User::factory()->create();
        $otherMo = MoisComptable::factory()->create(); // appartient à un autre user
        $tbl     = Tableau::factory()->for($otherMo)->create();
        $var     = Variable::factory()->for($tbl)->create();
        $regle   = RegleCalcul::factory()->for($var)->create();

        $this->actingAs($user)
             ->getJson("/api/regles-calcul/{$regle->id}")
             ->assertStatus(401);
    }

    /** @test */
    public function show_returns_rule_with_variable_relation()
    {
        $user    = User::factory()->create();
        $mois    = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tbl     = Tableau::factory()->for($mois)->create();
        $var     = Variable::factory()->for($tbl)->create();
        $regle   = RegleCalcul::factory()->for($var)->create();

        $response = $this->actingAs($user)
                         ->getJson("/api/regles-calcul/{$regle->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id'          => $regle->id,
                     'expression'  => $regle->expression,
                     'variable_id' => $var->id,
                 ]);
    }

    /** @test */
    public function test_on_peut_creer_une_regle_pour_sous_variable()
    {
        $user = User::factory()->create();
        $sv   = SousVariable::factory()->create(['user_id' => $user->id]);

        $regle = RegleCalcul::factory()
                ->forSousVariable($sv)
                ->create();

        $this->assertDatabaseHas('regle_calculs', [
            'id'                => $regle->id,
            'sous_variable_id'  => $sv->id,
            'variable_id'       => null,
            'user_id'           => $user->id,
            'expression'        => "{$sv->nom}.{$sv->id}",
        ]);
    }


    /** @test */
    public function store_creates_rule_when_expression_is_valid()
    {  
        $user    = User::factory()->create();
        $mois    = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tbl     = Tableau::factory()->for($mois, 'mois')->create();
        $var     = Variable::factory()->for($tbl,'tableau' )->create();

        dump($var->toArray());
        $this->assertDatabaseHas('variables', ['id' => $var->id]);

        // On mocke le service pour qu'il accepte l'expression
        $this->mock(RegleCalculService::class, function ($mock) {
            $mock->shouldReceive('validerExpression')
                 ->once()
                 ->with('A + B')
                 ->andReturnTrue();
        });

        $payload = [
            'variable_id' => $var->id,
            'expression'  => 'A + B',
        ];

        $response = $this->actingAs($user)
                         ->postJson('/api/regles-calcul', $payload);

        $response->dump(); 
        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'message'    => 'Règle créée avec succès.',
                     'expression' => 'A + B',
                 ]);

        $this->assertDatabaseHas('regle_calculs', [
            'variable_id' => $var->id,
            'expression'  => 'A + B',
        ]);
    }

    /** @test */
    public function store_returns_422_when_service_throws_exception()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tbl  = Tableau::factory()->for($mois)->create();
        $var  = Variable::factory()->for($tbl)->create();

        $this->mock(RegleCalculService::class, function ($mock) {
            $mock->shouldReceive('validerExpression')
                 ->once()
                 ->andThrow(new \Exception('Expression invalide'));
        });

        $payload = [
            'variable_id' => $var->id,
            'expression'  => 'INVALID',
        ];

        $this->actingAs($user)
             ->postJson('/api/regles-calcul', $payload)
             ->assertStatus(422)
             ->assertJsonFragment(['erreur' => 'Expression invalide']);
    }

    /** @test */
    public function update_modifies_existing_rule()
    {
        $user  = User::factory()->create();
        $mois  = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tbl   = Tableau::factory()->for($mois)->create();
        $var   = Variable::factory()->for($tbl)->create();
        $regle = RegleCalcul::factory()->for($var)->create(['expression' => 'X']);

        $this->mock(RegleCalculService::class, function ($mock) {
            $mock->shouldReceive('validerExpression')
                 ->once()
                 ->with('Y')
                 ->andReturnTrue();
        });

        $response = $this->actingAs($user)
                         ->putJson("/api/regles-calcul/{$regle->id}", [
                             'expression' => 'Y',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'message'    => 'Règle mise à jour.',
                     'expression' => 'Y',
                 ]);

        $this->assertDatabaseHas('regle_calculs', [
            'id'         => $regle->id,
            'expression' => 'Y',
        ]);
    }

    /** @test */
    public function destroy_deletes_rule_for_authorized_user()
    {
        $user  = User::factory()->create();
        $mois  = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tbl   = Tableau::factory()->for($mois)->create();
        $var   = Variable::factory()->for($tbl)->create();
        $regle = RegleCalcul::factory()->for($var)->create();

        $response = $this->actingAs($user)
                         ->deleteJson("/api/regles-calcul/{$regle->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Règle supprimée.']);

        $this->assertDatabaseMissing('regle_calculs', ['id' => $regle->id]);
    }

    /** @test */
    public function evaluer_returns_result_from_service()
    {
        $this->mock(RegleCalculService::class, function ($mock) {
            $mock->shouldReceive('evaluer')
                 ->once()
                 ->with('1+2')
                 ->andReturn(3);
        });

        $response = $this->postJson('/api/regles-calcul/evaluer', [
            'expression' => '1+2',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['resultat' => 3]);
    }

    /** @test */
    public function valider_returns_error_on_invalid_expression()
    {
        $this->mock(RegleCalculService::class, function ($mock) {
            $mock->shouldReceive('validerExpression')
                 ->once()
                 ->with('BAD')
                 ->andThrow(new \Exception('Non valide'));
        });

        $response = $this->postJson('/api/regles-calcul/valider', [
            'expression' => 'BAD',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Non valide']);
    }

    /** @test */
    public function variableRegle_and_sousVariableRegle_return_null_if_not_used()
    {
        $user  = User::factory()->create();

        $var = Variable::factory()->create();
        $sv  = SousVariable::factory()->create();

        $this->actingAs($user)
             ->getJson("/api/regles-calcul/variable/{$var->id}")
             ->assertStatus(200)
             ->assertJson(['variable_utilisee_par' => null]);

        $this->actingAs($user)
             ->getJson("/api/regles-calcul/sous-variable/{$sv->id}")
             ->assertStatus(200)
             ->assertJson(['sous_variable_utilisee_par' => null]);
    }
}
