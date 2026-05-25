<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Repositories;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Enums\ProductStatus;

class ProductRepository
{
    /**
     * Find active product with variants and relations.
     */
    public function findActiveWithVariants(int $id): ?Product
    {
        return Product::active()
            ->with(['variants' => fn ($q) => $q->where('is_active', true), 'category'])
            ->find($id);
    }

    /**
     * Find by slug with variants.
     */
    public function findBySlug(string $slug): ?Product
    {
        return Product::active()
            ->with(['variants' => fn ($q) => $q->where('is_active', true), 'category'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Find all products by category.
     */
    public function findByCategory(Category $category, array $options = []): \Illuminate\Pagination\Paginator|\Illuminate\Database\Eloquent\Collection
    {
        $query = Product::active()
            ->where('category_id', $category->id)
            ->with('variants')
            ->orderBy('created_at', 'desc');

        if (isset($options['featured']) && $options['featured']) {
            $query->where('is_featured', true);
        }

        if (isset($options['per_page'])) {
            return $query->paginate($options['per_page']);
        }

        return $query->get();
    }

    /**
     * Search products by term.
     */
    public function search(string $term, array $options = []): \Illuminate\Pagination\Paginator|\Illuminate\Database\Eloquent\Collection
    {
        $query = Product::active()
            ->with('variants')
            ->where(function ($q) use ($term) {
                $q->where('name', 'ilike', "%{$term}%")
                    ->orWhere('short_description', 'ilike', "%{$term}%")
                    ->orWhere('sku', 'ilike', "%{$term}%");
            })
            ->orderBy('created_at', 'desc');

        if (isset($options['per_page'])) {
            return $query->paginate($options['per_page']);
        }

        return $query->get();
    }

    /**
     * Get featured products.
     */
    public function getFeatured(int $limit = 12): \Illuminate\Database\Eloquent\Collection
    {
        return Product::active()
            ->featured()
            ->with('variants')
            ->limit($limit)
            ->get();
    }

    /**
     * Get new products.
     */
    public function getNew(int $limit = 12): \Illuminate\Database\Eloquent\Collection
    {
        return Product::active()
            ->with('variants')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get best sellers.
     */
    public function getBestSellers(int $limit = 12): \Illuminate\Database\Eloquent\Collection
    {
        return Product::active()
            ->with('variants')
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
