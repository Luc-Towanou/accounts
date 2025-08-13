<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => 'password' // supposons que la factory crée ce mot de passe
        ])->json('token');
    }

    /** @test */
    public function it_can_create_mois_comptable()
    {
        $response = $this->withToken($this->token)->postJson('/api/mois-comptables', [
            'nom' => 'Janvier 2025',
            'annee' => 2025
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nom' => 'Janvier 2025']);
    }

    /** @test */
    public function it_can_list_categories()
    {
        $response = $this->getJson('/api/categories');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_create_operation_sortie()
    {
        // Préparer une variable
        $variable = \App\Models\Variable::factory()->create();

        $payload = [
            'montant' => 100,
            'type' => 'sortie',
            'variable_id' => $variable->id
        ];

        $response = $this->withToken($this->token)->postJson('/api/operations', $payload);
        $response->assertStatus(201)
                 ->assertJsonFragment(['montant' => 100, 'type' => 'sortie']);
    }

    /** @test */
    public function it_can_evaluer_une_regle()
    {
        $payload = ['expression' => '=2*SV.1 + 3*SV.2'];

        $response = $this->withToken($this->token)->postJson('/api/regles-calcul/test/evaluer', $payload);
        $response->assertStatus(200)
                 ->assertJsonStructure(['resultat']);
    }
}
