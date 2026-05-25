# Domain Stubs - Template Usage Guide

This directory contains stub templates for generating domain layer classes following DDD-lite patterns.

## Available Stubs

### 1. Model.stub
Template for Eloquent models.

**Usage:**
```bash
# Create a new model in Catalog domain
php artisan make:model Domains/Catalog/Models/Category --no-migration
```

**Features:**
- Strict types declaration
- Fillable attributes
- Type casting

### 2. Repository.stub
Template for repository classes (data access layer).

**Usage:**
```bash
# Create repository (manual copy from stub)
cp app/Stubs/Repository.stub app/Domains/Catalog/Repositories/CategoryRepository.php
```

**Features:**
- Extends AbstractRepository
- setModel() implementation
- Custom finder methods

### 3. Service.stub
Template for service classes (business logic layer).

**Usage:**
```bash
# Create service (manual copy from stub)
cp app/Stubs/Service.stub app/Domains/Catalog/Services/CategoryService.php
```

**Features:**
- Extends AbstractService
- Constructor injection
- Business logic methods

### 4. Action.stub
Template for action classes (single responsibility operations).

**Usage:**
```bash
# Create action (manual copy from stub)
cp app/Stubs/Action.stub app/Domains/Order/Actions/CreateOrderAction.php
```

**Features:**
- Single execute() method
- Single responsibility principle
- No side effects

### 5. ValueObject.stub
Template for immutable value objects.

**Usage:**
```bash
# Create value object (manual copy from stub)
cp app/Stubs/ValueObject.stub app/Core/ValueObjects/Phone.php
```

**Features:**
- Immutable design
- Validation in constructor
- equals() method
- __toString() method

### 6. Enum.stub
Template for PHP native enums with labels.

**Usage:**
```bash
# Create enum (manual copy from stub)
cp app/Stubs/Enum.stub app/Domains/Catalog/Enums/CategoryStatus.php
```

**Features:**
- String-backed enums
- label() method for display
- Match expressions

## Quick Setup Commands

### Create a Complete Domain Model

```bash
# 1. Create the model
php artisan make:model Domains/Catalog/Models/Product --no-migration

# 2. Create migration manually
php artisan make:migration create_products_table

# 3. Create repository
cp app/Stubs/Repository.stub app/Domains/Catalog/Repositories/ProductRepository.php

# 4. Create service
cp app/Stubs/Service.stub app/Domains/Catalog/Services/ProductService.php

# 5. Create factory
php artisan make:factory ProductFactory --model="App\Domains\Catalog\Models\Product"

# 6. Create tests
php artisan make:test Domains/Catalog/Repositories/ProductRepositoryTest
php artisan make:test Domains/Catalog/Services/ProductServiceTest
```

### Edit Generated Files

After copying stub files, you need to:

1. Update the namespace
2. Add class name and dependencies
3. Implement custom methods

Example (Repository):
```php
<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Repositories;

use App\Core\AbstractRepository;
use App\Domains\Catalog\Models\Product;

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
}
```

## Naming Conventions

- **Models**: Singular (Product, Order, Customer)
- **Repositories**: ModelName + Repository (ProductRepository)
- **Services**: ModelName + Service (ProductService)
- **Actions**: VerbNoun + Action (CreateProductAction)
- **Enums**: ModelName + EnumType (ProductStatus)
- **ValueObjects**: ConceptName (Money, Email, SKU)

## Template Placeholders

Common placeholders found in stubs:

| Placeholder | Meaning |
|------------|---------|
| `{{ namespace }}` | PHP namespace |
| `{{ class }}` | Class name |
| `{{ model }}` | Model class name |
| `{{ repository }}` | Repository class name |
| `{{ modelNamespace }}` | Full model namespace |
| `{{ repositoryNamespace }}` | Full repository namespace |
| `{{ concept }}` | Concept name for ValueObjects |

## Best Practices

1. **Follow naming conventions** - Makes code predictable
2. **Use final classes** - Unless specifically designed to extend
3. **Type everything** - Arguments, returns, properties
4. **Keep repositories simple** - Only data access
5. **Keep services focused** - Business logic only
6. **One action per class** - Single responsibility
7. **Test thoroughly** - Unit + integration tests
8. **Document behavior** - PHPDoc comments
9. **Use immutables** - For value objects
10. **Validate in constructors** - For value objects

## Customization

To create custom stubs:

1. Copy an existing stub
2. Modify the template
3. Save with .stub extension
4. Reference in your code generation

Example custom stub:
```php
<?php

declare(strict_types=1);

namespace {{ namespace }};

use Illuminate\Database\Eloquent\Model;
use MyCustomTrait;

class {{ class }} extends Model
{
    use MyCustomTrait;
    
    protected $fillable = [];
}
```

## Artisan Command Generators

For Laravel-built-in generators, use:

```bash
# Models
php artisan make:model App/Domains/Catalog/Models/Product

# Migrations
php artisan make:migration create_products_table

# Factories
php artisan make:factory ProductFactory --model="App\Domains\Catalog\Models\Product"

# Tests
php artisan make:test Tests/Unit/Domains/Catalog/Services/ProductServiceTest
php artisan make:test Tests/Feature/Catalog/ProductControllerTest

# Controllers
php artisan make:controller App/Http/Web/Controllers/ProductController
php artisan make:controller App/Http/Admin/Controllers/ProductController

# Requests
php artisan make:request StoreProductRequest
php artisan make:request UpdateProductRequest

# Resources
php artisan make:resource ProductResource
php artisan make:resource ProductCollection
```

## Tips

- Keep stubs simple and minimal
- Focus on structure, not implementation details
- Include helpful comments in stubs
- Update stubs when patterns evolve
- Document your custom stubs
- Test generated code thoroughly
