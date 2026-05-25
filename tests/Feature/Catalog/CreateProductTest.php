<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use Tests\TestCase;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Attribute;
use App\Domains\Catalog\Models\AttributeOption;
use App\Domains\Catalog\Models\ProductAttributeValue;
use App\Domains\Catalog\Services\ProductService;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Enums\ProductStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateProductTest extends TestCase
{
    use RefreshDatabase;

    protected ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProductService::class);
    }

    /**
     * Test creating a simple product.
     */
    public function test_can_create_simple_product(): void
    {
        $category = Category::factory()->create();

        $product = $this->service->createSimple([
            'category_id' => $category->id,
            'name' => 'Simple Product',
            'price' => 10000, // 100.00
            'cost' => 5000,
            'stock' => 50,
            'sku' => 'PROD-001',
        ]);

        $this->assertTrue($product->type->isSimple());
        $this->assertEquals(ProductStatus::DRAFT, $product->status);
        $this->assertCount(1, $product->variants);
        $this->assertEquals(10000, $product->variants->first()->price);
    }

    /**
     * Test creating a configurable product with variants.
     */
    public function test_can_create_configurable_product(): void
    {
        $category = Category::factory()->create();
        $sizeAttr = Attribute::factory()->create(['slug' => 'taille']);
        $colorAttr = Attribute::factory()->create(['slug' => 'couleur']);

        $sizeM = AttributeOption::factory()->for($sizeAttr)->create(['value' => 'm']);
        $sizeL = AttributeOption::factory()->for($sizeAttr)->create(['value' => 'l']);
        $colorRed = AttributeOption::factory()->for($colorAttr)->create(['value' => 'red']);
        $colorBlue = AttributeOption::factory()->for($colorAttr)->create(['value' => 'blue']);

        $product = $this->service->createConfigurable(
            [
                'category_id' => $category->id,
                'name' => 'Configurable Product',
            ],
            [
                ['price' => 10000, 'cost' => 5000, 'stock' => 50, 'attributes' => [$sizeAttr->id => $sizeM->id, $colorAttr->id => $colorRed->id]],
                ['price' => 11000, 'cost' => 5500, 'stock' => 40, 'attributes' => [$sizeAttr->id => $sizeM->id, $colorAttr->id => $colorBlue->id]],
                ['price' => 12000, 'cost' => 6000, 'stock' => 30, 'attributes' => [$sizeAttr->id => $sizeL->id, $colorAttr->id => $colorRed->id]],
                ['price' => 13000, 'cost' => 6500, 'stock' => 20, 'attributes' => [$sizeAttr->id => $sizeL->id, $colorAttr->id => $colorBlue->id]],
            ]
        );

        $this->assertTrue($product->type->isConfigurable());
        $this->assertCount(4, $product->variants);
        $this->assertEquals(10000, $product->variants->min('price'));
        $this->assertEquals(13000, $product->variants->max('price'));
    }

    /**
     * Test SKU is unique.
     */
    public function test_sku_is_unique(): void
    {
        $category = Category::factory()->create();

        $product1 = $this->service->createSimple([
            'category_id' => $category->id,
            'name' => 'Product 1',
            'price' => 10000,
            'sku' => 'UNIQUE-SKU-001',
        ]);

        $this->expectException(\Exception::class);
        
        $this->service->createSimple([
            'category_id' => $category->id,
            'name' => 'Product 2',
            'price' => 10000,
            'sku' => 'UNIQUE-SKU-001',
        ]);
    }

    /**
     * Test slug is unique.
     */
    public function test_slug_is_unique(): void
    {
        $category = Category::factory()->create();

        $product1 = $this->service->createSimple([
            'category_id' => $category->id,
            'name' => 'Same Product Name',
            'price' => 10000,
        ]);

        $product2 = $this->service->createSimple([
            'category_id' => $category->id,
            'name' => 'Same Product Name',
            'price' => 10000,
        ]);

        $this->assertNotEquals($product1->slug, $product2->slug);
    }
}
