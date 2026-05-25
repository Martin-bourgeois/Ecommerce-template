<?php

declare(strict_types=1);

namespace App\Domains\Analytics\Services;

use App\Domains\Order\Models\Order;
use App\Domains\Catalog\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    private const CACHE_DURATION = 5 * 60; // 5 minutes

    /**
     * Obtient le revenu pour une période donnée.
     */
    public function getRevenue(Carbon $from, Carbon $to): float
    {
        $cacheKey = "analytics:revenue:{$from->format('Y-m-d')}:{$to->format('Y-m-d')}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($from, $to) {
            return (float) Order::whereBetween('created_at', [$from, $to])
                ->where('status', '!=', 'cancelled')
                ->sum('total_cents') / 100; // Convert from cents to currency
        });
    }

    /**
     * Obtient les commandes pour une période donnée.
     */
    public function getOrdersCount(Carbon $from, Carbon $to): int
    {
        $cacheKey = "analytics:orders_count:{$from->format('Y-m-d')}:{$to->format('Y-m-d')}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($from, $to) {
            return Order::whereBetween('created_at', [$from, $to])
                ->where('status', '!=', 'cancelled')
                ->count();
        });
    }

    /**
     * Obtient les nouveaux clients pour une période donnée.
     */
    public function getNewCustomers(Carbon $from, Carbon $to): int
    {
        $cacheKey = "analytics:new_customers:{$from->format('Y-m-d')}:{$to->format('Y-m-d')}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($from, $to) {
            return User::whereBetween('created_at', [$from, $to])->count();
        });
    }

    /**
     * Obtient les produits les plus vendus pour une période donnée.
     */
    public function getTopProducts(Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        $cacheKey = "analytics:top_products:{$from->format('Y-m-d')}:{$to->format('Y-m-d')}:{$limit}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($from, $to, $limit) {
            return Product::selectRaw('products.*, SUM(order_items.qty) as total_qty')
                ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$from, $to])
                ->where('orders.status', '!=', 'cancelled')
                ->groupBy('products.id')
                ->orderByDesc('total_qty')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Obtient les statistiques des clients pour une période donnée.
     */
    public function getCustomerStats(Carbon $from, Carbon $to): array
    {
        $cacheKey = "analytics:customer_stats:{$from->format('Y-m-d')}:{$to->format('Y-m-d')}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($from, $to) {
            $totalOrders = Order::whereBetween('created_at', [$from, $to])
                ->where('status', '!=', 'cancelled')
                ->count();

            $totalRevenue = Order::whereBetween('created_at', [$from, $to])
                ->where('status', '!=', 'cancelled')
                ->sum('total_cents') / 100; // Convert from cents to currency

            $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

            return [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'avg_order_value' => $avgOrderValue,
                'new_customers' => $this->getNewCustomers($from, $to),
            ];
        });
    }

    /**
     * Obtient les données de revenu pour les 7 derniers jours.
     */
    public function getRevenueByDay(int $days = 7): array
    {
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $key = $date->format('Y-m-d');
            $cacheKey = "analytics:revenue_day:{$key}";

            $data[$date->format('d/m')] = Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($date) {
                return (float) Order::whereBetween('created_at', [$date->startOfDay(), $date->endOfDay()])
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_cents') / 100; // Convert from cents to currency
            });
        }

        return $data;
    }

    /**
     * Clears all analytics cache.
     */
    public function clearCache(): void
    {
        Cache::flush();
    }
}
