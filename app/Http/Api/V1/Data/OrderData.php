<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class OrderData extends Data
{
    /**
     * @param OrderItemData[] $items
     */
    public function __construct(
        public int $id,
        public string $order_number,
        public int $user_id,
        public string $status,
        public array $items,
        public float $subtotal,
        public float $tax,
        public float $total,
        public ?float $discount,
        public string $payment_method,
        public string $payment_status,
        public array $shipping_address,
        public ?string $tracking_number,
        public ?string $carrier,
        public Carbon $created_at,
        public Carbon $updated_at,
        public ?Carbon $shipped_at,
        public ?Carbon $delivered_at,
    ) {}

    /**
     * Créer une OrderData depuis un modèle Order
     */
    public static function fromModel($order): self
    {
        $items = $order->items()
            ->get()
            ->map(fn($item) => OrderItemData::fromModel($item))
            ->toArray();

        return new self(
            id: $order->id,
            order_number: $order->order_number,
            user_id: $order->user_id,
            status: $order->status,
            items: $items,
            subtotal: (float) $order->subtotal,
            tax: (float) $order->tax,
            total: (float) $order->total,
            discount: $order->discount > 0 ? (float) $order->discount : null,
            payment_method: $order->payment_method,
            payment_status: $order->payment_status,
            shipping_address: [
                'street' => $order->shipping_street,
                'city' => $order->shipping_city,
                'postal_code' => $order->shipping_postal_code,
                'country' => $order->shipping_country,
            ],
            tracking_number: $order->tracking_number,
            carrier: $order->carrier,
            created_at: $order->created_at,
            updated_at: $order->updated_at,
            shipped_at: $order->shipped_at,
            delivered_at: $order->delivered_at,
        );
    }
}

/**
 * Data pour un article de commande
 */
class OrderItemData extends Data
{
    public function __construct(
        public int $product_id,
        public string $product_name,
        public string $sku,
        public float $price,
        public int $quantity,
        public float $total,
    ) {}

    public static function fromModel($item): self
    {
        return new self(
            product_id: $item->product_id,
            product_name: $item->product_name,
            sku: $item->sku,
            price: (float) $item->price,
            quantity: $item->quantity,
            total: (float) $item->total,
        );
    }
}
