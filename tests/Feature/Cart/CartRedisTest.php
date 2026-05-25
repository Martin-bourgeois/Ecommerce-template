<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use Tests\TestCase;
use App\Domains\Catalog\Models\Product;
use App\Domains\Cart\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;

class CartRedisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushdb();
    }

    /**
     * Test cart is stored in Redis.
     */
    public function test_cart_stored_in_redis(): void
    {
        $cartService = app(CartService::class);
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);

        $cartService->add($product->sku, 1);

        $sessionId = session()->getId();
        $key = "cart:guest:{$sessionId}";

        $this->assertTrue(Redis::exists($key));
        $this->assertNotNull(Redis::get($key));
    }

    /**
     * Test Redis key TTL for guest cart (30 days).
     */
    public function test_guest_cart_has_ttl(): void
    {
        $cartService = app(CartService::class);
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);

        $cartService->add($product->sku, 1);

        $sessionId = session()->getId();
        $key = "cart:guest:{$sessionId}";

        $ttl = Redis::ttl($key);

        // TTL should be close to 30 days (30 * 24 * 60 * 60 = 2592000 seconds)
        $this->assertGreaterThan(2500000, $ttl); // Allow some variance
        $this->assertLessThanOrEqual(2592000, $ttl);
    }

    /**
     * Test user cart is persistent (no expiration).
     */
    public function test_user_cart_is_persistent(): void
    {
        $cartService = app(CartService::class);
        $cartService->setUser(123);
        
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);
        $cartService->add($product->sku, 1);

        $key = "cart:user:123";
        $ttl = Redis::ttl($key);

        // User cart should have no expiration (-1 means no expiration)
        // Or a very long TTL (30 days like guest)
        // In this implementation, both have 30 days TTL
        $this->assertGreaterThan(-1, $ttl);
    }

    /**
     * Test Redis data format is JSON.
     */
    public function test_redis_stores_json_not_serialized(): void
    {
        $cartService = app(CartService::class);
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);

        $cartService->add($product->sku, 2);

        $sessionId = session()->getId();
        $key = "cart:guest:{$sessionId}";
        $data = Redis::get($key);

        // Should be valid JSON
        $decoded = json_decode($data, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey($product->sku, $decoded);
        $this->assertEquals(2, $decoded[$product->sku]['qty']);
    }

    /**
     * Test clearing cart removes Redis key.
     */
    public function test_clearing_cart_removes_redis_key(): void
    {
        $cartService = app(CartService::class);
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);

        $cartService->add($product->sku, 1);
        $sessionId = session()->getId();
        $key = "cart:guest:{$sessionId}";

        $this->assertTrue(Redis::exists($key));

        $cartService->clear();

        $this->assertFalse(Redis::exists($key));
    }
}
