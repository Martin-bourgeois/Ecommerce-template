<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use Tests\TestCase;
use App\Domains\Catalog\Models\Product;
use App\Domains\Cart\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartMergeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test merging guest cart into user cart.
     */
    public function test_can_merge_guest_cart_into_user_cart(): void
    {
        $guestService = app(CartService::class);
        $product1 = Product::factory()->create(['status' => 'published', 'type' => 'simple']);
        $product2 = Product::factory()->create(['status' => 'published', 'type' => 'simple']);

        // Add items as guest
        $guestService->add($product1->sku, 2);
        $guestService->add($product2->sku, 1);

        $guestSessionId = session()->getId();
        $userId = 1; // Simulated user ID

        // Merge guest into user cart
        $guestService->merge($guestSessionId, $userId);

        // Create user service instance
        $userService = app(CartService::class);
        $userService->setUser($userId);

        $content = $userService->getContent();
        $this->assertCount(2, $content);
    }

    /**
     * Test merge keeps max quantity on conflict.
     */
    public function test_merge_keeps_max_quantity_on_conflict(): void
    {
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);

        // Guest has product with qty 2
        $guestService = app(CartService::class);
        $guestService->add($product->sku, 2);

        $guestSessionId = session()->getId();
        $userId = 1;

        // User already has product with qty 5
        $userService = app(CartService::class);
        $userService->setUser($userId);
        $userService->add($product->sku, 5);

        // Merge
        $guestService->merge($guestSessionId, $userId);

        // User should have max(2, 5) = 5
        $content = $userService->getContent();
        $item = $content->first();
        $this->assertEquals(5, $item->qty);
    }

    /**
     * Test merge with empty guest cart.
     */
    public function test_merge_with_empty_guest_cart(): void
    {
        $guestService = app(CartService::class);
        $guestSessionId = session()->getId();
        $userId = 1;

        $userService = app(CartService::class);
        $userService->setUser($userId);

        // Add to user cart first
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);
        $userService->add($product->sku, 1);

        // Merge empty guest
        $guestService->merge($guestSessionId, $userId);

        $content = $userService->getContent();
        $this->assertCount(1, $content);
    }
}
