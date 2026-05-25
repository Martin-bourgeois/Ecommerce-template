<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Enums\ProductStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Pagination\Paginator;

class CatalogController extends Controller
{
    /**
     * Affiche la liste des produits avec filtres
     */
    public function index(Request $request): View
    {
        $query = Product::query()
            ->where('status', ProductStatus::ACTIVE)
            ->with(['categories', 'ratings']);

        // Filtre par recherche
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtre par catégorie
        if ($categorySlug = $request->get('category')) {
            $query->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Filtre par gamme de prix
        if ($minPrice = $request->get('min_price')) {
            $query->where('price', '>=', (int)$minPrice);
        }
        if ($maxPrice = $request->get('max_price')) {
            $query->where('price', '<=', (int)$maxPrice);
        }

        // Tri
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'popular' => $query->orderBy('view_count', 'desc'),
            'rating' => $query->leftJoin('product_ratings', 'products.id', '=', 'product_ratings.product_id')
                ->selectRaw('products.*, AVG(product_ratings.rating) as avg_rating')
                ->groupBy('products.id')
                ->orderBy('avg_rating', 'desc'),
            default => $query->orderBy('created_at', 'desc'), // newest
        };

        // Pagination
        $products = $query->paginate(12);

        // Récupérer les catégories pour le filtre
        $categories = Category::where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('catalog.index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    /**
     * Affiche le détail d'un produit
     */
    public function show(Product $product): View
    {
        // Rediriger si inactif
        if ($product->status !== ProductStatus::ACTIVE) {
            abort(404);
        }

        // Charger les relations
        $product->load([
            'categories',
            'variants',
            'reviews' => function ($q) {
                $q->where('is_approved', true)->latest()->limit(10);
            },
            'ratings',
        ]);

        // Produits similaires (même catégorie)
        $relatedProducts = Product::query()
            ->where('status', ProductStatus::ACTIVE)
            ->where('id', '!=', $product->id)
            ->whereHas('categories', function ($q) use ($product) {
                $q->whereIn('categories.id', $product->categories->pluck('id'));
            })
            ->limit(4)
            ->get();

        // Statistiques avis
        $reviewStats = [
            'average_rating' => $product->ratings()->avg('rating') ?? 0,
            'total_reviews' => $product->reviews->count(),
            'by_stars' => [
                5 => $product->ratings()->where('rating', 5)->count(),
                4 => $product->ratings()->where('rating', 4)->count(),
                3 => $product->ratings()->where('rating', 3)->count(),
                2 => $product->ratings()->where('rating', 2)->count(),
                1 => $product->ratings()->where('rating', 1)->count(),
            ],
        ];

        return view('catalog.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'reviewStats' => $reviewStats,
        ]);
    }
}
