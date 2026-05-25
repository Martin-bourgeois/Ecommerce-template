<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Promotion\Models\Promotion;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil
     */
    public function __invoke(): View
    {
        // Produits en vedette (8 derniers marqués comme featured)
        $featuredProducts = Product::query()
            ->where('status', ProductStatus::ACTIVE)
            ->where('is_featured', true)
            ->latest()
            ->limit(8)
            ->get();

        // Si moins de 8 produits en vedette, remplir avec les plus récents
        if ($featuredProducts->count() < 8) {
            $needed = 8 - $featuredProducts->count();
            $additional = Product::query()
                ->where('status', ProductStatus::ACTIVE)
                ->where('is_featured', false)
                ->latest()
                ->limit($needed)
                ->get();
            $featuredProducts = $featuredProducts->merge($additional);
        }

        // Nouveautés (8 plus récents produits)
        $newArrivals = Product::query()
            ->where('status', ProductStatus::ACTIVE)
            ->latest()
            ->limit(8)
            ->get();

        // Produits les plus vendus (top 8 basé sur nombre de commandes)
        $bestsellers = Product::query()
            ->where('status', ProductStatus::ACTIVE)
            ->withCount('orderItems as sales_count')
            ->orderByDesc('sales_count')
            ->limit(8)
            ->get();

        // Catégories populaires (top 6 par nombre de produits)
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount('products')
            ->orderByDesc('products_count')
            ->limit(6)
            ->get();

        // Promotions actives (bannières marketing)
        $activePromotions = Promotion::query()
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return view('home', [
            'featuredProducts' => $featuredProducts,
            'newArrivals' => $newArrivals,
            'bestsellers' => $bestsellers,
            'categories' => $categories,
            'activePromotions' => $activePromotions,
        ]);
    }
}
