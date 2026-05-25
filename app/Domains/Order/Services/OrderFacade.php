<?php

declare(strict_types=1);

namespace App\Domains\Order\Services;

use App\Domains\Cart\Services\CartFacade;
use App\Domains\Order\Enums\OrderStatus;
use App\Models\User;
use App\Domains\Order\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Facade pour la création de commandes depuis les composants Livewire.
 */
class OrderFacade
{
    public function __construct(
        private CartFacade $cartFacade,
    ) {}

    /**
     * Crée une commande avec les données de checkout.
     */
    public function createOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            // Get cart with items
            $cart = $this->cartFacade->getCart($user);
            
            if ($cart->items()->count() === 0) {
                throw new \RuntimeException('Cart is empty');
            }

            // Calculate totals from cart items
            $subtotal = 0;
            $items_data = [];
            
            foreach ($cart->items()->with('product')->get() as $item) {
                $subtotal += $item->price_cents * $item->quantity;
                $items_data[] = [
                    'product_id' => $item->product_id,
                    'sku' => $item->product->sku ?? 'SKU-' . $item->product_id,
                    'name' => $item->product->name,
                    'qty' => $item->quantity,
                    'price_cents' => $item->price_cents,
                    'total_cents' => $item->price_cents * $item->quantity,
                ];
            }
            
            $tax_cents = (int)($subtotal * 0.20);
            $shipping_cents = $subtotal > 10000 ? 0 : 999; // 10€ = 1000 cents, €9.99 = 999 cents
            $total_cents = $subtotal + $tax_cents + $shipping_cents;

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . time() . '-' . $user->id,
                'status' => OrderStatus::PENDING_PAYMENT->value,
                'first_name' => $data['first_name'] ?? $user->name,
                'last_name' => $data['last_name'] ?? '',
                'email' => $data['email'] ?? $user->email,
                'phone' => $data['phone'] ?? '',
                'street' => $data['street'] ?? '',
                'city' => $data['city'] ?? '',
                'postal_code' => $data['postal_code'] ?? '',
                'country' => $data['country'] ?? '',
                'shipping_method' => $data['shipping_method'] ?? 'standard',
                'payment_method' => $data['payment_method'] ?? 'card',
                'subtotal_cents' => $subtotal,
                'tax_cents' => $tax_cents,
                'shipping_cents' => $shipping_cents,
                'total_cents' => $total_cents,
            ]);

            // Copy cart items to order
            foreach ($items_data as $item) {
                $order->items()->create($item);
            }

            // Clear cart
            $this->cartFacade->clearCart($user);

            return $order;
        });
    }
}

