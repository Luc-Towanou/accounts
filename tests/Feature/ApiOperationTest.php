<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MoisComptable;
use App\Models\Tableau;
use App\Models\Variable;
use App\Models\SousVariable;
use App\Models\Operation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiOperationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_user_operations_grouped_by_nature()
    {
        $user = User::factory()->create();

        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);

        $tableau = Tableau::factory()->for($mois)->create();
        
        $var = Variable::factory()
            ->for($tableau)
            ->create([
                'type'    => 'simple',
                'user_id' => $user->id,
            ]);

        $var2 = Variable::factory()
            ->for($tableau)
            ->create([
                'type'    => 'sous-tableau',
                'user_id' => $user->id,
            ]);

        $sv = SousVariable::create([
            'nom'         => 'Enfant',
            'user_id'     => $user->id,
            'variable_id' => $var2->id,
        ]);


        // Création « naturelles » sur le modèle parent
        $var->operations()->create(['nature' => 'sortie', 'montant' => 20 ]);
        $sv->operations()->create( ['nature' => 'sortie', 'montant' => 22 ]);
        $var->operations()->create(['nature' => 'entree', 'montant' => 14 ]);

        $response = $this->actingAs($user)->getJson('/api/operations');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'sorties',
                     'entrees',
                 ])
                 ->assertJsonCount(2, 'sorties')
                 ->assertJsonCount(1, 'entrees');
    }

    /** @test */
    public function store_requires_exactly_one_target()
    {
        $user = User::factory()->create();
        $payload = [
            'montant' => 100,
            'nature'  => 'sortie',
            // ni variable_id ni sous_variable_id
        ];

        $response = $this->actingAs($user)->postJson('/api/operations', $payload);

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'error' => "L'opération doit être liée à une variable ou une sous-variable."
                 ]);

        // cas des deux cibles en même temps
        $var = Variable::factory()->create();
        $sv  = SousVariable::factory()->create();

        $payload = [
            'montant'           => 100,
            'nature'            => 'sortie',
            'variable_id'       => $var->id,
            'sous_variable_id'  => $sv->id,
        ];
        $response = $this->actingAs($user)->postJson('/api/operations', $payload);

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'error' => "Une opération ne peut pas appartenir à la fois à une variable et à une sous-variable."
                 ]);
    }

    /** @test */
    public function store_rejects_direct_link_to_sous_tableau_variable()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tableau = Tableau::factory()->for($mois)->create();
        $var = Variable::factory()->for($tableau)->create(['type' => 'sous-tableau']);

        $payload = [
            'montant'     => 50,
            'nature'      => 'entree',
            'variable_id' => $var->id,
        ];

        $response = $this->actingAs($user)->postJson('/api/operations', $payload);

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'error' => "L'opération ne peut être directement relié à la variable elle même. Choisissez plutot une sous-variable."
                 ]);
    }

    /** @test */
    public function store_creates_operation_and_returns_proper_payload()
    {
        $user = User::factory()->create();
        $mois = MoisComptable::factory()->create(['user_id' => $user->id]);
        $tableau = Tableau::factory()->for($mois)->create();
        $var = Variable::factory()->for($tableau)->create(['type' => 'simple']);
        $sv  = SousVariable::factory()->for($var)->create();

        $payloadVar = [
            'montant'     => 120,
            'nature'      => 'sortie',
            'variable_id' => $var->id,
        ];

        $response = $this->actingAs($user)->postJson('/api/operations', $payloadVar);

        $response->assertStatus(201)
                 ->assertJsonPath('variable.id', $var->id);

        $payloadSv = [
            'montant'          => 80,
            'nature'           => 'entree',
            'sous_variable_id' => $sv->id,
        ];

        $response = $this->actingAs($user)->postJson('/api/operations', $payloadSv);

        $response->assertStatus(201)
                 ->assertJsonPath('sousVariable.id', $sv->id);
    }

    /** @test */
    public function show_rejets_operation_without_login()
    {
        $op = Operation::factory()->create();

        $response = $this->getJson("/api/operations/{$op->id}");

        $response->assertStatus(401)
                 ->assertJsonFragment([
                                        'message' => "Unauthenticated."
                                     ]);
    }


    /** @test */
    public function show_returns_operation_with_relationsuse_var()
    {

        $user = User::factory()->create();
        $token = $user->createToken('api_token')->plainTextToken;

        $var = Variable::factory()->for($user)->create(['type' => 'simple']);

        // $op = Operation::factory()->for($var)->create();
        $op = Operation::create ([
            'variable_id' => $var->id,
            'montant' => 120,
            'nature' => 'sortie'
        ]);
        $response = $this ->withHeader('Authorization', "Bearer {$token}")
                          ->getJson("/api/operations/{$op->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $op->id]);
    }
    /** @test */
    public function show_returns_operation_with_relations_use_sv()
    {
        
        $user = User::factory()->create();
        $token = $user->createToken('api_token')->plainTextToken;
        $var = Variable::factory()->for($user)->create(['type' => 'sous-tableau']);
        $sv  = SousVariable::factory()->for($var)->create();
        
        $op = Operation::create ([
            'variable_id' => $sv->id,
            'montant' => 120,
            'nature' => 'entree'
        ]);
        // $op = Operation::factory()->for($sv)->create();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson("/api/operations/{$op->id}");

        $response->assertStatus(200)
                   ->assertJsonFragment(['id' => $op->id]);
    }

    /** @test */
    public function update_modifies_operation_and_triggers_recalculate()
    {
        $user = User::factory()->create();
        $var = Variable::factory()->for($user)->create(['type' => 'sous-tableau']);
        $sv  = SousVariable::factory()->for($var)->create();
        
        $op = Operation::factory()->create([
            'variable_id' => null,
            'sous_variable_id' => $sv->id,
            'montant' => 200,
            'description' => 'Old Desc',
        ]);

        $payload = ['montant' => 301, 'description' => 'New Desc'];

        $response = $this->actingAs($user)
                         ->putJson("/api/operations/{$op->id}", $payload);

        $response->assertStatus(200)
                 ->assertJsonPath('operation.montant', '301.00')
                 ->assertJsonPath('operation.description', 'New Desc');
    }

    /** @test */
    public function destroy_deletes_operation_and_returns_message()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api_token')->plainTextToken;
        $var = Variable::factory()->for($user)->create(['type' => 'simple']);
        $op = Operation::factory()->for($var)->create();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                          ->actingAs($user)->deleteJson("/api/operations/{$op->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Opération supprimée avec succès.']);

        $this->assertDatabaseMissing('operations', ['id' => $op->id]);
    }
}
