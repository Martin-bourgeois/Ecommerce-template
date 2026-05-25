<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Http\Api\V1\Data\ProductData;
use App\Http\Api\V1\Requests\ProductSearchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;

/**
 * Contrôleur pour les produits
 * Endpoints: liste, détail, recherche, filtres
 */
class ProductController
{
    /**
     * Lister les produits avec pagination cursor
     *
     * @route GET /api/v1/products
     */
    public function index(ProductSearchRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 20;

        $query = Product::where('status', ProductStatus::ACTIVE);

        // Filtrer par catégorie
        if ($validated['category_id'] ?? null) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $validated['category_id']));
        }

        // Filtrer par prix
        if ($validated['min_price'] ?? null) {
            $query->where('price', '>=', $validated['min_price']);
        }
        if ($validated['max_price'] ?? null) {
            $query->where('price', '<=', $validated['max_price']);
        }

        // Rechercher par texte
        if ($validated['search'] ?? null) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        // Trier
        $sortBy = $validated['sort_by'] ?? 'newest';
        $sortOrder = $validated['sort_order'] ?? 'desc';

        match ($sortBy) {
            'price' => $query->orderBy('price', $sortOrder),
            'name' => $query->orderBy('name', $sortOrder),
            'popular' => $query->orderByRaw('(SELECT COUNT(*) FROM order_items WHERE product_id = products.id) DESC'),
            'rating' => $query->orderByRaw('(SELECT AVG(rating) FROM reviews WHERE product_id = products.id) DESC'),
            default => $query->orderBy('created_at', $sortOrder),
        };

        // Paginer avec cursor
        $products = $query->cursorPaginate($perPage);

        return response()->json([
            'data' => $products->items() ? array_map(
                fn($p) => ProductData::fromModelMinimal($p),
                $products->items()
            ) : [],
            'pagination' => [
                'per_page' => $perPage,
                'path' => $products->path(),
                'next_cursor' => $products->nextCursor()?->encode(),
                'prev_cursor' => $products->prevCursor()?->encode(),
            ],
        ], 200);
    }

    /**
     * Obtenir les détails d'un produit
     *
     * @route GET /api/v1/products/{product}
     */
    public function show(Product $product): JsonResponse
    {
        if ($product->status !== ProductStatus::ACTIVE) {
            return response()->json([
                'message' => 'Product not found',
                'errors' => [
                    'product' => ['Le produit n\'existe pas'],
                ],
            ], 404);
        }

        return response()->json([
            'data' => ProductData::fromModel($product),
        ], 200);
    }

    /**
     * Obtenir les produits en avant
     *
     * @route GET /api/v1/products/featured
     */
    public function featured(): JsonResponse
    {
        $products = Product::where('status', ProductStatus::ACTIVE)
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => array_map(
                fn($p) => ProductData::fromModelMinimal($p),
                $products->toArray()
            ),
        ], 200);
    }

    /**
     * Obtenir les produits similaires
     *
     * @route GET /api/v1/products/{product}/related
     */
    public function related(Product $product): JsonResponse
    {
        if ($product->status !== ProductStatus::ACTIVE) {
            return response()->json([
                'message' => 'Product not found',
                'errors' => [
                    'product' => ['Le produit n\'existe pas'],
                ],
            ], 404);
        }

        // Obtenir les produits de la même catégorie
        $relatedProducts = $product->categories()
            ->first()
            ?->products()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => $relatedProducts ? array_map(
                fn($p) => ProductData::fromModelMinimal($p),
                $relatedProducts->toArray()
            ) : [],
        ], 200);
    }
}
