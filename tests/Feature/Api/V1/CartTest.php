<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Tests\TestCase;

/**
 * Tests pour l'API Cart
 */
class CartTest extends TestCase
{
    /**
     * Test obtenir le panier
     */
    public function test_get_cart(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->getJson('/api/v1/cart', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'items',
                    'subtotal',
                    'tax',
                    'total',
                    'coupon_code',
                    'discount',
                ],
            ]);
    }

    /**
     * Test ajouter au panier
     */
    public function test_add_to_cart(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->postJson('/api/v1/cart/add', [
            'product_id' => 1,
            'quantity' => 2,
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Product added to cart')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'items',
                    'total',
                ],
            ]);
    }

    /**
     * Test ajouter au panier échoue sans authentification
     */
    public function test_add_to_cart_requires_auth(): void
    {
        $response = $this->postJson('/api/v1/cart/add', [
            'product_id' => 1,
            'quantity' => 2,
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated');
    }

    /**
     * Test validation du formulaire add to cart
     */
    public function test_add_to_cart_validates_input(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->postJson('/api/v1/cart/add', [
            'product_id' => 'invalid',
            'quantity' => 0,
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    /**
     * Test mettre à jour le panier
     */
    public function test_update_cart_item(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->patchJson('/api/v1/cart/items/1', [
            'quantity' => 5,
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Cart item updated');
    }

    /**
     * Test supprimer du panier
     */
    public function test_remove_from_cart(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->deleteJson('/api/v1/cart/items/1', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Cart item removed');
    }

    /**
     * Test vider le panier
     */
    public function test_clear_cart(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->deleteJson('/api/v1/cart', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Cart cleared');
    }

    /**
     * Test appliquer un coupon
     */
    public function test_apply_coupon(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        // Sans coupon valide, devrait échouer avec 422
        $response = $this->postJson('/api/v1/cart/coupon', [
            'coupon_code' => 'INVALIDCOUPON',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(422);
    }
}
