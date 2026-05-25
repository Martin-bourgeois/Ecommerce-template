<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Tests\TestCase;

/**
 * Tests pour l'API Orders
 */
class OrdersTest extends TestCase
{
    /**
     * Test obtenir l'historique des commandes
     */
    public function test_get_orders_history(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->getJson('/api/v1/orders', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ],
            ]);
    }

    /**
     * Test obtenir les détails d'une commande
     */
    public function test_show_order_detail(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->getJson('/api/v1/orders/1', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'user_id',
                    'status',
                    'items',
                    'total',
                ],
            ]);
    }

    /**
     * Test créer une commande
     */
    public function test_create_order(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->postJson('/api/v1/orders', [
            'shipping_address_id' => 1,
            'payment_method' => 'card',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Order created successfully')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'status',
                ],
            ]);
    }

    /**
     * Test créer une commande échoue sans authentification
     */
    public function test_create_order_requires_auth(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'shipping_address_id' => 1,
            'payment_method' => 'card',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated');
    }

    /**
     * Test annuler une commande
     */
    public function test_cancel_order(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->postJson('/api/v1/orders/1/cancel', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Order cancelled successfully');
    }

    /**
     * Test obtenir le suivi d'une commande
     */
    public function test_get_order_tracking(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->getJson('/api/v1/orders/1/tracking', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'order_id',
                    'status',
                    'tracking_number',
                    'carrier',
                    'estimated_delivery',
                    'timeline',
                ],
            ]);
    }

    /**
     * Test filtrer par status
     */
    public function test_filter_orders_by_status(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->getJson('/api/v1/orders?status=shipped', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200);
    }
}
