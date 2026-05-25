# Getting Started - Creating First Models and Migrations

This guide walks you through creating your first domain models and migrations.

## Example: Creating a Product Model

### Step 1: Generate Migration

```bash
docker-compose exec app php artisan make:migration create_products_table
```

Edit `database/migrations/YYYY_MM_DD_HHMMSS_create_products_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();
            
            $table->index('sku');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

### Step 2: Create Model

Create file `app/Domains/Catalog/Models/Product.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model implements InteractsWithMedia
{
    use InteractsWithMedia;
    use HasTags;
    use HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'cost',
        'stock',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->skipGenerate();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
}
```

### Step 3: Create Repository

Create file `app/Domains/Catalog/Repositories/ProductRepository.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Repositories;

use App\Core\AbstractRepository;
use App\Domains\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Collection;

final class ProductRepository extends AbstractRepository
{
    protected function setModel(): void
    {
        $this->model = app(Product::class);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model->where('sku', $sku)->first();
    }

    public function findActive(): Collection
    {
        return $this->model->where('status', 'active')->get();
    }

    public function findInStock(): Collection
    {
        return $this->model->where('stock', '>', 0)->get();
    }
}
```

### Step 4: Create Service

Create file `app/Domains/Catalog/Services/ProductService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Services;

use App\Core\AbstractService;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Repositories\ProductRepository;

final class ProductService extends AbstractService
{
    public function __construct(
        private ProductRepository $repository
    ) {}

    public function getProduct(int $id): ?Product
    {
        return $this->repository->find($id);
    }

    public function createProduct(array $data): Product
    {
        return $this->repository->create($data);
    }

    public function updateProduct(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function decrementStock(int $productId, int $quantity): bool
    {
        $product = $this->repository->find($productId);

        if (!$product || $product->stock < $quantity) {
            return false;
        }

        return $this->repository->update($productId, [
            'stock' => $product->stock - $quantity,
        ]);
    }
}
```

### Step 5: Run Migrations

```bash
docker-compose exec app php artisan migrate
```

### Step 6: Create Controller

Create file `app/Http/Web/Controllers/Catalog/ProductController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Web\Controllers\Catalog;

use Illuminate\View\View;
use App\Domains\Catalog\Services\ProductService;
use App\Http\Controllers\Controller;

final class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function show(int $id): View
    {
        $product = $this->productService->getProduct($id);

        if (!$product) {
            abort(404);
        }

        return view('catalog.show', ['product' => $product]);
    }
}
```

### Step 7: Test It

```bash
# Generate test class
docker-compose exec app php artisan make:test Domains/Catalog/Services/ProductServiceTest

# Edit tests/Domains/Catalog/Services/ProductServiceTest.php
```

```php
<?php

declare(strict_types=1);

namespace Tests\Domains\Catalog\Services;

use Tests\TestCase;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Services\ProductService;
use App\Domains\Catalog\Repositories\ProductRepository;

class ProductServiceTest extends TestCase
{
    private ProductService $service;
    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(ProductRepository::class);
        $this->service = app(ProductService::class);
    }

    public function test_creates_product(): void
    {
        $data = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'stock' => 10,
        ];

        $product = $this->service->createProduct($data);

        $this->assertEquals('Test Product', $product->name);
        $this->assertDatabaseHas('products', ['sku' => 'TEST-001']);
    }

    public function test_decrements_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $result = $this->service->decrementStock($product->id, 3);

        $this->assertTrue($result);
        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_fails_to_decrement_invalid_quantity(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $result = $this->service->decrementStock($product->id, 10);

        $this->assertFalse($result);
        $this->assertEquals(5, $product->fresh()->stock);
    }
}
```

Run tests:

```bash
docker-compose exec app php artisan test
```

## Next Steps

Follow this pattern to create models for other domains:

1. **Catalog Domain**
   - Category
   - Attribute
   - ProductVariant
   - Review

2. **Customer Domain**
   - CustomerProfile
   - Address
   - Preference

3. **Order Domain**
   - Order
   - OrderItem
   - OrderStatus

4. **Promotion Domain**
   - Promotion
   - Coupon
   - PromotionRule

5. **Support Domain**
   - SupportTicket
   - TicketMessage

6. **RMA Domain**
   - ReturnRequest
   - ReturnItem

Each should follow:
- Migration file
- Model class with strict types
- Repository for data access
- Service for business logic
- Factory for testing
- Tests

## Testing Checklist

- ✅ Unit test repository methods
- ✅ Unit test service business logic
- ✅ Integration test controllers
- ✅ Feature test complete workflows
- ✅ Mock external services

## Tips

- Use factories for test data: `Product::factory()->create()`
- Test error cases and edge cases
- Keep repositories simple (data access only)
- Keep services focused (single responsibility)
- Use enums for statuses (e.g., `ProductStatus::ACTIVE`)
- Use value objects for domain concepts (Money, Email, SKU)
