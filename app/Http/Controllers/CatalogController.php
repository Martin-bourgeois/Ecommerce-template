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
        $query = Product::query()->where('status', ProductStatus::ACTIVE);

        // Filtrer par catégorie
        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->get('category'));
            });
        }

        // Filtrer par prix
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->get('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->get('max_price'));
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Tri
        $sort = $request->get('sort', 'newest');
        $query = match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular' => $query->withCount('orderItems as sales_count')->orderByDesc('sales_count'),
            'rating' => $query->withCount('ratings as avg_rating')->orderByDesc('avg_rating'),
            default => $query->latest(),
        };

        $products = $query->paginate(12);
        $categories = Category::where('is_active', true)->get();

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
