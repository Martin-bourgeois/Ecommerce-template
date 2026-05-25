<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Data;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Enums\ProductStatus;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $description,
        public float $price,
        public ?float $discounted_price,
        public int $quantity,
        public string $sku,
        public ?string $image_url,
        public array $images,
        public array $categories,
        public int $rating,
        public int $reviews_count,
        public bool $is_active,
        public bool $is_featured,
        public Carbon $created_at,
        public Carbon $updated_at,
    ) {}

    /**
     * Créer une ProductData depuis un modèle Product
     */
    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            name: $product->name,
            slug: $product->slug,
            description: $product->description,
            price: (float) $product->price,
            discounted_price: $product->discounted_price ? (float) $product->discounted_price : null,
            quantity: $product->quantity,
            sku: $product->sku,
            image_url: $product->getFirstMediaUrl('products') ?: null,
            images: $product->getMedia('products')
                ->map(fn($media) => [
                    'url' => $media->getUrl(),
                    'alt' => $media->getCustomProperty('alt') ?? $product->name,
                ])
                ->toArray(),
            categories: $product->categories()
                ->get()
                ->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ])
                ->toArray(),
            rating: (int) $product->avgRating(),
            reviews_count: (int) $product->reviews()->count(),
            is_active: $product->status === ProductStatus::ACTIVE,
            is_featured: $product->is_featured,
            created_at: $product->created_at,
            updated_at: $product->updated_at,
        );
    }

    /**
     * Créer une ProductData pour listing (moins de détails)
     */
    public static function fromModelMinimal(Product $product): self
    {
        return new self(
            id: $product->id,
            name: $product->name,
            slug: $product->slug,
            description: substr($product->description, 0, 200),
            price: (float) $product->price,
            discounted_price: $product->discounted_price ? (float) $product->discounted_price : null,
            quantity: $product->quantity,
            sku: $product->sku,
            image_url: $product->getFirstMediaUrl('products') ?: null,
            images: [],
            categories: [],
            rating: (int) $product->avgRating(),
            reviews_count: (int) $product->reviews()->count(),
            is_active: $product->status === ProductStatus::ACTIVE,
            is_featured: $product->is_featured,
            created_at: $product->created_at,
            updated_at: $product->updated_at,
        );
    }
}
