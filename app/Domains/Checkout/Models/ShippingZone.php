<?php

declare(strict_types=1);

namespace App\Domains\Checkout\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = [
        'name',
        'countries', // JSON array
        'base_cost', // in cents
        'weight_cost', // cost per kg in cents
        'free_shipping_threshold', // in cents (order total)
        'is_active',
    ];

    protected $casts = [
        'countries' => 'array',
        'base_cost' => 'integer',
        'weight_cost' => 'integer',
        'free_shipping_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Check if country is in this zone.
     */
    public function hasCountry(string $countryCode): bool
    {
        return in_array(strtoupper($countryCode), $this->countries ?? []);
    }

    /**
     * Calculate shipping cost for given weight and order total.
     *
     * @param float $weightKg Total weight in kilograms
     * @param int $orderTotalCents Order total in cents
     * @return int Shipping cost in cents
     */
    public function calculateCost(float $weightKg, int $orderTotalCents): int
    {
        // Check if free shipping threshold is met
        if ($this->free_shipping_threshold > 0 && $orderTotalCents >= $this->free_shipping_threshold) {
            return 0;
        }

        // Calculate: base cost + (weight * cost per kg)
        $weightCost = (int) round($weightKg * $this->weight_cost);

        return $this->base_cost + $weightCost;
    }
}
