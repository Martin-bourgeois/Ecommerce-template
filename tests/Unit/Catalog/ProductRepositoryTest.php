<?php

declare(strict_types=1);

namespace Tests\Unit\Catalog;

use Tests\TestCase;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ProductRepository::class);
    }

    /**
     * Test find active product with variants.
     */
    public function test_find_active_product_with_variants(): void
    {
        $product = Product::factory()->active()->create();
        ProductVariant::factory()->for($product)->count(3)->create();

        $found = $this->repository->findActiveWithVariants($product->id);

        $this->assertNotNull($found);
        $this->assertCount(3, $found->variants);
    }

    /**
     * Test find active product does not return inactive.
     */
    public function test_find_active_does_not_return_draft(): void
    {
        $product = Product::factory()->draft()->create();

        $found = $this->repository->findActiveWithVariants($product->id);

        $this->assertNull($found);
    }

    /**
     * Test find by slug.
     */
    public function test_find_by_slug(): void
    {
        $product = Product::factory()->active()->create();

        $found = $this->repository->findBySlug($product->slug);

        $this->assertNotNull($found);
        $this->assertEquals($product->id, $found->id);
    }

    /**
     * Test find by category.
     */
    public function test_find_by_category(): void
    {
        $category = Category::factory()->create();
        
        Product::factory()->for($category)->active()->count(5)->create();
        Product::factory()->active()->count(3)->create(); // Different categories

        $products = $this->repository->findByCategory($category);

        $this->assertCount(5, $products);
        $products->each(fn ($p) => $this->assertEquals($category->id, $p->category_id));
    }

    /**
     * Test search by term.
     */
    public function test_search_by_term(): void
    {
        Product::factory()->active()->create(['name' => 'iPhone 15 Pro']);
        Product::factory()->active()->create(['name' => 'Samsung Galaxy']);
        Product::factory()->active()->create(['name' => 'iPhone 14']);

        $results = $this->repository->search('iPhone');

        $this->assertCount(2, $results);
        $results->each(fn ($p) => $this->assertStringContainsString('iPhone', $p->name));
    }

    /**
     * Test get featured products.
     */
    public function test_get_featured(): void
    {
        Product::factory()->featured()->active()->count(5)->create();
        Product::factory()->active()->count(10)->create();

        $featured = $this->repository->getFeatured(10);

        $this->assertCount(5, $featured);
        $featured->each(fn ($p) => $this->assertTrue($p->is_featured));
    }

    /**
     * Test get new products.
     */
    public function test_get_new(): void
    {
        Product::factory()->active()->count(15)->create();

        $new = $this->repository->getNew(12);

        $this->assertCount(12, $new);
        // Most recent should be first
        $this->assertTrue($new[0]->created_at >= $new[11]->created_at);
    }

    /**
     * Test get best sellers (by view count).
     */
    public function test_get_best_sellers(): void
    {
        Product::factory()->active()->create(['view_count' => 100]);
        Product::factory()->active()->create(['view_count' => 50]);
        Product::factory()->active()->create(['view_count' => 200]);

        $sellers = $this->repository->getBestSellers(10);

        $this->assertEquals(200, $sellers[0]->view_count);
        $this->assertEquals(100, $sellers[1]->view_count);
        $this->assertEquals(50, $sellers[2]->view_count);
    }
}
