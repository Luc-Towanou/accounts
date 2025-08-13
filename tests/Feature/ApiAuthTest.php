<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_user_can_register_and_receive_otp()
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Inscription réussie, vérifiez votre email pour le code OTP.']);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);

        Mail::assertSent(OtpMail::class);
    }

    /** @test */
    public function user_can_verify_email_with_otp()
    {
        $user = User::factory()->create([
            'otp' => '123456',
            'otp_expires_at' => Carbon::now()->addMinutes(10),
            'email_verified_at' => null
        ]);

        $response = $this->postJson('/api/auth/verifymailotp', [
            'email' => $user->email,
            'otp' => '123456'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Email confirmé avec succès. vous pouvez desormais vous connecter via la page de login.']);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    /** @test */
    public function user_can_login_with_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret123')
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['user', 'token']);
    }

    /** @test */
    public function user_can_login_with_otp()
    {
        $user = User::factory()->create([
            'otp' => '654321',
            'otp_expires_at' => Carbon::now()->addMinutes(10),
            'email_verified_at' => now()
        ]);

        $response = $this->postJson('/api/auth/login/otp', [
            'email' => $user->email,
            'otp' => '654321'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'user', 'token']);
    }

    /** @test */
    public function user_can_resend_otp()
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/resendotp', [
            'email' => $user->email
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Un nouveau code OTP a été envoyé.']);

        Mail::assertSent(OtpMail::class);
    }

    /** @test */
    public function user_can_reset_password_with_otp()
    {
        $user = User::factory()->create([
            'otp' => '111222',
            'otp_expires_at' => Carbon::now()->addMinutes(10)
        ]);

        $response = $this->postJson('/api/auth/password/resetotp', [
            'email' => $user->email,
            'otp' => '111222',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Mot de passe réinitialisé avec succès.']);

        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/auth/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Déconnexion réussie.']);
    }
}
