<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use Tests\TestCase;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductStockTest extends TestCase
{
    use RefreshDatabase;

    protected ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProductService::class);
    }

    /**
     * Test cannot create product with negative stock.
     */
    public function test_cannot_create_with_negative_stock(): void
    {
        $category = Category::factory()->create();

        $this->expectException(\Exception::class);

        $this->service->createSimple([
            'category_id' => $category->id,
            'name' => 'Product',
            'price' => 10000,
            'stock' => -10,
        ]);
    }

    /**
     * Test stock update.
     */
    public function test_can_update_stock(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 50]);

        $this->service->updateStock($variant, 100);

        $this->assertEquals(100, $variant->fresh()->stock);
    }

    /**
     * Test cannot update to negative stock.
     */
    public function test_cannot_update_to_negative_stock(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 50]);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->updateStock($variant, -10);
    }

    /**
     * Test deduct stock.
     */
    public function test_can_deduct_stock(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 50]);

        $result = $this->service->deductStock($variant, 20);

        $this->assertTrue($result);
        $this->assertEquals(30, $variant->fresh()->stock);
    }

    /**
     * Test cannot deduct more than available.
     */
    public function test_cannot_deduct_more_than_available(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 50]);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->deductStock($variant, 100);
    }

    /**
     * Test reserve stock.
     */
    public function test_can_reserve_stock(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 100, 'reserved_stock' => 0]);

        $result = $this->service->reserveStock($variant, 30);

        $this->assertTrue($result);
        $this->assertEquals(30, $variant->fresh()->reserved_stock);
    }

    /**
     * Test available stock calculation.
     */
    public function test_available_stock_calculation(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 100, 'reserved_stock' => 30]);

        $available = $variant->getAvailableStock();

        $this->assertEquals(70, $available);
    }

    /**
     * Test variant is in stock.
     */
    public function test_variant_is_in_stock(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 50]);

        $this->assertTrue($variant->isInStock());
    }

    /**
     * Test variant out of stock.
     */
    public function test_variant_out_of_stock(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 0]);

        $this->assertFalse($variant->isInStock());
    }

    /**
     * Test configurable product stock aggregation.
     */
    public function test_configurable_product_stock_aggregation(): void
    {
        $product = Product::factory()->configurable()->create();
        
        ProductVariant::factory()->for($product)->create(['stock' => 50, 'reserved_stock' => 10]);
        ProductVariant::factory()->for($product)->create(['stock' => 30, 'reserved_stock' => 5]);
        ProductVariant::factory()->for($product)->create(['stock' => 20, 'reserved_stock' => 0]);

        $totalStock = $product->getTotalStock();

        $this->assertEquals(85, $totalStock); // (50-10) + (30-5) + (20-0)
    }
}
