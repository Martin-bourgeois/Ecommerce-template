<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use Tests\TestCase;
use App\Domains\Checkout\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShippingZoneTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test shipping zone contains country.
     */
    public function test_shipping_zone_contains_country(): void
    {
        $zone = ShippingZone::create([
            'name' => 'France',
            'countries' => ['FR', 'MC'],
            'base_cost' => 500,
            'weight_cost' => 100,
            'is_active' => true,
        ]);

        $this->assertTrue($zone->hasCountry('FR'));
        $this->assertTrue($zone->hasCountry('MC'));
        $this->assertFalse($zone->hasCountry('DE'));
    }

    /**
     * Test shipping cost calculation with weight.
     */
    public function test_shipping_cost_calculation_with_weight(): void
    {
        $zone = ShippingZone::create([
            'name' => 'France',
            'countries' => ['FR'],
            'base_cost' => 500, // 5€
            'weight_cost' => 100, // 1€ per kg
            'is_active' => true,
        ]);

        // 2kg, any order total
        $cost = $zone->calculateCost(2.0, 1000);

        // base_cost (500) + weight (2 * 100) = 700 cents
        $this->assertEquals(700, $cost);
    }

    /**
     * Test free shipping above threshold.
     */
    public function test_free_shipping_above_threshold(): void
    {
        $zone = ShippingZone::create([
            'name' => 'France',
            'countries' => ['FR'],
            'base_cost' => 500,
            'weight_cost' => 100,
            'free_shipping_threshold' => 5000, // Free above 50€
            'is_active' => true,
        ]);

        // Order total 60€ (6000 cents), above threshold
        $cost = $zone->calculateCost(1.0, 6000);

        $this->assertEquals(0, $cost);
    }

    /**
     * Test charges shipping below threshold.
     */
    public function test_charges_shipping_below_threshold(): void
    {
        $zone = ShippingZone::create([
            'name' => 'France',
            'countries' => ['FR'],
            'base_cost' => 500,
            'weight_cost' => 100,
            'free_shipping_threshold' => 5000,
            'is_active' => true,
        ]);

        // Order total 30€ (3000 cents), below threshold
        $cost = $zone->calculateCost(1.0, 3000);

        // Should charge normal cost
        $this->assertGreaterThan(0, $cost);
    }

    /**
     * Test no threshold (always charge).
     */
    public function test_always_charges_with_no_threshold(): void
    {
        $zone = ShippingZone::create([
            'name' => 'France',
            'countries' => ['FR'],
            'base_cost' => 500,
            'weight_cost' => 100,
            'free_shipping_threshold' => null,
            'is_active' => true,
        ]);

        $cost = $zone->calculateCost(0.5, 10000);

        // base_cost (500) + weight (0.5 * 100) = 550
        $this->assertEquals(550, $cost);
    }
}
