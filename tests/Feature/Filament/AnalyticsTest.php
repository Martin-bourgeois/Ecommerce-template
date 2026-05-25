<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Domains\Order\Models\Order;
use App\Models\User;
use App\Domains\Analytics\Services\AnalyticsService;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class AnalyticsTest extends TestCase
{
    private User $adminUser;
    private AnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::firstOrCreate(['name' => 'admin']);

        // Create admin user
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->analyticsService = app(AnalyticsService::class);
    }

    public function test_get_revenue_returns_float(): void
    {
        $revenue = $this->analyticsService->getRevenue(
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        $this->assertIsFloat($revenue);
    }

    public function test_get_orders_count_returns_integer(): void
    {
        $count = $this->analyticsService->getOrdersCount(
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        $this->assertIsInt($count);
    }

    public function test_get_revenue_by_day_returns_array(): void
    {
        $data = $this->analyticsService->getRevenueByDay(7);

        $this->assertIsArray($data);
        $this->assertCount(7, $data);
    }

    public function test_get_customer_stats_returns_array_with_required_keys(): void
    {
        $stats = $this->analyticsService->getCustomerStats(
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_orders', $stats);
        $this->assertArrayHasKey('total_revenue', $stats);
        $this->assertArrayHasKey('avg_order_value', $stats);
        $this->assertArrayHasKey('new_customers', $stats);
    }

    public function test_get_top_products_returns_collection(): void
    {
        $products = $this->analyticsService->getTopProducts(
            now()->subMonth(),
            now()
        );

        $this->assertIsObject($products);
    }

    public function test_cache_is_working(): void
    {
        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        // First call
        $revenue1 = $this->analyticsService->getRevenue($from, $to);

        // Second call should be from cache
        $revenue2 = $this->analyticsService->getRevenue($from, $to);

        $this->assertEquals($revenue1, $revenue2);
    }

    public function test_clear_cache_clears_all(): void
    {
        // Set some cache
        \Illuminate\Support\Facades\Cache::put('test_key', 'test_value', 60);

        // Clear cache
        $this->analyticsService->clearCache();

        // Verify it's cleared
        $this->assertNull(\Illuminate\Support\Facades\Cache::get('test_key'));
    }
}
