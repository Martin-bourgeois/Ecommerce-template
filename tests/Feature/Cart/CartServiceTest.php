<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use Tests\TestCase;
use App\Domains\Catalog\Models\Product;
use App\Domains\Cart\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);
    }

    /**
     * Test adding item to cart.
     */
    public function test_can_add_item_to_cart(): void
    {
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);
        $initialCount = $this->cartService->count();

        $this->cartService->add($product->sku, 2);

        $this->assertEquals($initialCount + 2, $this->cartService->count());
        $this->assertFalse($this->cartService->isEmpty());
    }

    /**
     * Test adding multiple items.
     */
    public function test_can_add_multiple_items(): void
    {
        $product1 = Product::factory()->create(['status' => 'published', 'type' => 'simple']);
        $product2 = Product::factory()->create(['status' => 'published', 'type' => 'simple']);

        $this->cartService->add($product1->sku, 1);
        $this->cartService->add($product2->sku, 2);

        $this->assertEquals(3, $this->cartService->count());
    }

    /**
     * Test updating item quantity.
     */
    public function test_can_update_item_quantity(): void
    {
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);
        $this->cartService->add($product->sku, 1);

        $this->cartService->update($product->sku, 5);

        $this->assertEquals(5, $this->cartService->count());
    }

    /**
     * Test removing item from cart.
     */
    public function test_can_remove_item(): void
    {
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);
        $this->cartService->add($product->sku, 2);

        $this->cartService->remove($product->sku);

        $this->assertTrue($this->cartService->isEmpty());
    }

    /**
     * Test clearing entire cart.
     */
    public function test_can_clear_cart(): void
    {
        $product1 = Product::factory()->create(['status' => 'published', 'type' => 'simple']);
        $product2 = Product::factory()->create(['status' => 'published', 'type' => 'simple']);

        $this->cartService->add($product1->sku, 1);
        $this->cartService->add($product2->sku, 2);

        $this->assertFalse($this->cartService->isEmpty());

        $this->cartService->clear();

        $this->assertTrue($this->cartService->isEmpty());
    }

    /**
     * Test cannot add more than available stock.
     */
    public function test_cannot_add_more_than_stock(): void
    {
        $product = Product::factory()->create([
            'status' => 'published',
            'type' => 'simple',
            'stock' => 5,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->cartService->add($product->sku, 10);
    }

    /**
     * Test get cart content.
     */
    public function test_can_get_cart_content(): void
    {
        $product = Product::factory()->create(['status' => 'published', 'type' => 'simple']);
        $this->cartService->add($product->sku, 2, ['color' => 'red']);

        $content = $this->cartService->getContent();

        $this->assertCount(1, $content);
        $item = $content->first();
        $this->assertEquals($product->sku, $item->sku);
        $this->assertEquals(2, $item->qty);
        $this->assertEquals(['color' => 'red'], $item->options);
    }

    /**
     * Test cart total calculation.
     */
    public function test_cart_total_calculation(): void
    {
        $product1 = Product::factory()->create([
            'status' => 'published',
            'type' => 'simple',
            'price' => 1000, // 10€ in cents
        ]);
        $product2 = Product::factory()->create([
            'status' => 'published',
            'type' => 'simple',
            'price' => 2000, // 20€ in cents
        ]);

        $this->cartService->add($product1->sku, 2);
        $this->cartService->add($product2->sku, 1);

        $total = $this->cartService->total();

        // (10€ * 2) + (20€ * 1) = 40€ = 4000 cents
        $this->assertEquals(4000, $total);
    }
}
