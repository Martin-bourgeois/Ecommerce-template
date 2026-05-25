<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Data;

use Spatie\LaravelData\Data;

class CartData extends Data
{
    /**
     * @param CartItemData[] $items
     */
    public function __construct(
        public string $id,
        public int $user_id,
        public array $items,
        public float $subtotal,
        public float $tax,
        public float $total,
        public ?string $coupon_code,
        public ?float $discount,
    ) {}

    /**
     * Créer une CartData depuis un modèle Cart
     * 
     * @param object $cart Cart model with items(), id, user_id, discount, coupon_code properties
     */
    public static function fromModel(object $cart): self
    {
        $items = [];
        
        // Essayer d'obtenir les items si la relation existe
        if (method_exists($cart, 'items') && is_callable([$cart, 'items'])) {
            try {
                $items = $cart->items()
                    ->with('product')
                    ->get()
                    ->map(fn($item) => CartItemData::fromModel($item))
                    ->toArray();
            } catch (\Exception $e) {
                // Si erreur, items reste vide
                $items = [];
            }
        }

        $subtotal = (float) array_sum(array_map(
            fn($item) => $item['price'] * $item['quantity'],
            $items
        ));

        $tax = $subtotal * (config('shop.tax_rate') ?? 0.20);
        $discount = $cart->discount ?? 0;
        $total = $subtotal + $tax - $discount;

        return new self(
            id: (string) ($cart->id ?? 'cart_unknown'),
            user_id: $cart->user_id ?? 0,
            items: $items,
            subtotal: $subtotal,
            tax: $tax,
            total: max(0, $total),
            coupon_code: $cart->coupon_code ?? null,
            discount: $discount > 0 ? $discount : null,
        );
    }
}

/**
 * Data pour un article du panier
 */
class CartItemData extends Data
{
    public function __construct(
        public int $product_id,
        public string $product_name,
        public string $product_image,
        public float $price,
        public int $quantity,
        public float $total,
    ) {}

    public static function fromModel($item): self
    {
        $price = (float) ($item->product->discounted_price ?? $item->product->price);

        return new self(
            product_id: $item->product_id,
            product_name: $item->product->name,
            product_image: $item->product->getFirstMediaUrl('products') ?: '',
            price: $price,
            quantity: $item->quantity,
            total: $price * $item->quantity,
        );
    }
}
