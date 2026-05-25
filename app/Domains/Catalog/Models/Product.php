<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\MediaLibrary\HasMedia;
use App\Traits\HasProductMedia;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use HasSlug;
    use HasProductMedia;
    use Searchable;
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'type',
        'status',
        'price',
        'cost',
        'weight',
        'sku',
        'barcode',
        'manufacturer',
        'brand',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'is_featured',
    ];

    protected $casts = [
        'type' => ProductType::class,
        'status' => ProductStatus::class,
        'is_featured' => 'boolean',
        'view_count' => 'integer',
        'price' => 'integer',
        'cost' => 'integer',
        'weight' => 'integer',
    ];

    /**
     * Get total stock from variants as an accessor.
     */
    protected function stock(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->getTotalStock(),
        );
    }

    /**
     * Get the slug options.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Get category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all categories (many-to-many relationship).
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product')
            ->withTimestamps();
    }

    /**
     * Get variants.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /**
     * Get active variants.
     */
    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    /**
     * Get ratings.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get reviews.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(\App\Domains\Catalog\Models\Review::class);
    }

    /**
     * Get order items (for sales count).
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(\App\Domains\Order\Models\OrderItem::class);
    }

    /**
     * Get average rating.
     */
    public function getRatingAvg(): float
    {
        return $this->ratings()
            ->approved()
            ->avg('rating') ?? 0;
    }

    /**
     * Get popularity (sales count or view count).
     */
    public function getPopularity(): int
    {
        return $this->view_count ?? 0;
    }

    /**
     * Scope: Active products only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', ProductStatus::ACTIVE->value);
    }

    /**
     * Scope: Featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: By category.
     */
    public function scopeByCategory($query, Category $category)
    {
        return $query->where('category_id', $category->id);
    }

    /**
     * Scope: With average rating from approved reviews.
     */
    public function scopeWithAvgRating($query)
    {
        return $query->withAvg('reviews', 'rating');
    }

    /**
     * Get total stock from variants.
     */
    public function getTotalStock(): int
    {
        return $this->activeVariants()
            ->sum(DB::raw('stock - reserved_stock'));
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        if ($this->type->isSimple()) {
            return $this->variants->first()?->stock > 0;
        }

        return $this->getTotalStock() > 0;
    }

    /**
     * Get price (use variant price for simple products).
     */
    public function getPrice(): ?int
    {
        if ($this->type->isConfigurable()) {
            return $this->variants()->min('price');
        }

        return $this->price ?? $this->variants->first()?->price;
    }

    /**
     * Increment view count.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Get searchable array for Meilisearch
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'brand' => $this->brand,
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name ?? '',
            'price' => $this->getPrice() ?? 0,
            'in_stock' => $this->isInStock(),
            'rating_avg' => round($this->getRatingAvg(), 2),
            'popularity' => $this->getPopularity(),
            'created_at' => $this->created_at?->timestamp ?? 0,
        ];
    }
}
