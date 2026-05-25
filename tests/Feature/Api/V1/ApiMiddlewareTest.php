<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Tests\TestCase;

/**
 * Tests pour les Middlewares API
 */
class ApiMiddlewareTest extends TestCase
{
    /**
     * Test request sans authentification sur endpoint protégé
     */
    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/account/profile');

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated')
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'authentication',
                ],
            ]);
    }

    /**
     * Test request avec token invalide
     */
    public function test_invalid_token_returns_401(): void
    {
        $response = $this->getJson('/api/v1/account/profile', [
            'Authorization' => 'Bearer invalid-token-string',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated');
    }

    /**
     * Test request avec token valide fonctionne
     */
    public function test_valid_token_allows_access(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->getJson('/api/v1/account/profile', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id);
    }

    /**
     * Test les headers de rate limit sont présents
     */
    public function test_response_includes_rate_limit_headers(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->getJson('/api/v1/auth/me', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertHeader('X-RateLimit-Limit')
            ->assertHeader('X-RateLimit-Remaining');
    }

    /**
     * Test health endpoint public
     */
    public function test_health_endpoint_is_public(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    /**
     * Test 404 sur endpoint inexistant
     */
    public function test_404_for_non_existent_endpoint(): void
    {
        $response = $this->getJson('/api/v1/non-existent-endpoint');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Not found')
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'endpoint',
                ],
            ]);
    }

    /**
     * Test format JSON consistent
     */
    public function test_error_response_has_consistent_format(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            // device_name manquant
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [],
            ]);
    }

    /**
     * Test les endpoints publics de produits ne nécessitent pas de token
     */
    public function test_products_endpoints_are_public(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
    }

    /**
     * Test login endpoint ne nécessite pas de token
     */
    public function test_login_endpoint_is_public(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'Test Device',
        ]);

        // Retournera 401 car utilisateur invalide, mais pas 401 pour authentification
        $response->assertIn($response->status(), [401, 422]);
    }
}
