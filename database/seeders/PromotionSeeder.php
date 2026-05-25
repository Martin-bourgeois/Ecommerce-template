<?php

namespace Database\Seeders;

use App\Domains\Promotion\Models\Coupon;
use App\Domains\Promotion\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Summer Sale - 20% off on all products (stackable, not limited)
        $summer = Promotion::create([
            'name' => 'Soldes d\'été',
            'slug' => 'summer-sale',
            'description' => '20% de réduction sur toutes les commandes',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 20,
            'conditions' => [
                'min_amount' => 0,
            ],
            'is_stackable' => true,
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(2),
            'priority' => 10,
        ]);

        // 2. VIP Discount - 15% off non-stackable (highest priority wins)
        $vip = Promotion::create([
            'name' => 'Réduction VIP',
            'slug' => 'vip-discount',
            'description' => 'Réduction exclusive pour nos clients VIP',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 15,
            'conditions' => [
                'customer_group' => 'vip',
            ],
            'is_stackable' => false,
            'is_active' => true,
            'priority' => 100, // High priority
        ]);

        // 3. First Order - Free Shipping
        $firstOrder = Promotion::create([
            'name' => 'Première commande - Livraison gratuite',
            'slug' => 'first-order-free-shipping',
            'description' => 'Livraison offerte pour les nouveaux clients',
            'type' => 'free_shipping',
            'target' => 'order',
            'value' => 0,
            'conditions' => [
                'first_order' => true,
            ],
            'is_stackable' => true,
            'is_active' => true,
            'priority' => 5,
        ]);

        // 4. Min Amount - 10€ off on orders over 100€
        $minAmount = Promotion::create([
            'name' => 'Réduction sur commande minimum',
            'slug' => 'min-amount-discount',
            'description' => '-10€ sur les commandes de 100€ minimum',
            'type' => 'fixed_amount',
            'target' => 'order',
            'value' => 10,
            'conditions' => [
                'min_amount' => 100,
            ],
            'is_stackable' => true,
            'is_active' => true,
            'priority' => 15,
        ]);

        // 5. Min Quantity - 5% off on orders with 5+ items
        $minQty = Promotion::create([
            'name' => 'Réduction en volume',
            'slug' => 'min-qty-discount',
            'description' => '5% de réduction à partir de 5 articles',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 5,
            'conditions' => [
                'min_qty' => 5,
            ],
            'is_stackable' => true,
            'is_active' => true,
            'priority' => 8,
        ]);

        // Create coupons for some promotions
        Coupon::create([
            'promotion_id' => $summer->id,
            'code' => 'SUMMER2026',
            'usage_limit' => 100,
            'per_customer_limit' => 3,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(2),
        ]);

        Coupon::create([
            'promotion_id' => $minAmount->id,
            'code' => 'SAVE10',
            'usage_limit' => 50,
            'per_customer_limit' => 1,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(1),
        ]);

        Coupon::create([
            'promotion_id' => $vip->id,
            'code' => 'VIP15',
            'usage_limit' => null, // Unlimited
            'per_customer_limit' => null, // Unlimited per customer
            'valid_from' => now(),
            'valid_until' => now()->addYear(),
        ]);

        $this->command->info('✓ Promotions seeder completed');
    }
}
