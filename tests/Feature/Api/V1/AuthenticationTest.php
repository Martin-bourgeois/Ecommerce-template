<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Tests\TestCase;

/**
 * Tests pour l'API Authentication
 */
class AuthenticationTest extends TestCase
{
    /**
     * Test login avec email et mot de passe
     */
    public function test_login_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'Test Device',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'token',
                    'token_type',
                ],
            ])
            ->assertJsonPath('data.token_type', 'Bearer');
    }

    /**
     * Test login échoue avec mauvais email
     */
    public function test_login_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'password123',
            'device_name' => 'Test Device',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('errors.authentication.0', 'Email ou mot de passe incorrect');
    }

    /**
     * Test login échoue avec mauvais mot de passe
     */
    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
            'device_name' => 'Test Device',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('errors.authentication.0', 'Email ou mot de passe incorrect');
    }

    /**
     * Test register crée un nouveau compte
     */
    public function test_register_creates_new_user(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    /**
     * Test register échoue avec email déjà utilisé
     */
    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'Cet email est déjà utilisé');
    }

    /**
     * Test logout révoque le token
     */
    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->postJson('/api/v1/auth/logout', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Logged out successfully');

        // Vérifier que le token est révoqué
        $this->postJson('/api/v1/auth/me', [], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(401);
    }

    /**
     * Test me() retourne les infos de l'utilisateur connecté
     */
    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->getJson('/api/v1/auth/me', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    /**
     * Test requête sans token retourne 401
     */
    public function test_protected_endpoint_requires_token(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated');
    }
}
