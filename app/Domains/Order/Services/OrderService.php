<?php

declare(strict_types=1);

namespace App\Domains\Order\Services;

use App\Domains\Cart\Services\CartService;
use App\Domains\Catalog\Models\Product;
use App\Domains\Checkout\Services\CheckoutService;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Events\OrderCancelled;
use App\Domains\Order\Events\OrderCreated;
use App\Domains\Order\Events\OrderStatusChanged;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private CartService $cartService,
        private CheckoutService $checkoutService,
        private OrderNumberGenerator $numberGenerator,
    ) {
    }

    /**
     * Create order from checkout session.
     *
     * Full transaction: validates cart, addresses, generates order number,
     * creates order and items, records status history, and fires events.
     *
     * @param User $user
     * @return Order
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function createFromCheckout(User $user): Order
    {
        // Validate prerequisites
        $cart = $this->cartService->getContent($user->id);
        $summary = $this->checkoutService->getSummary();

        if ($cart->isEmpty()) {
            throw new \InvalidArgumentException('Cart is empty');
        }

        if (!$summary->shippingAddress || !$summary->billingAddress) {
            throw new \InvalidArgumentException('Shipping and billing addresses are required');
        }

        // Atomic transaction
        return DB::transaction(function () use ($user, $cart, $summary) {
            // Generate unique order number
            $orderNumber = $this->numberGenerator->generate();

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'status' => OrderStatus::PENDING_PAYMENT->value,
                'subtotal_cents' => $summary->subtotalCents,
                'shipping_cents' => $summary->shippingCents,
                'tax_cents' => $summary->taxCents,
                'total_cents' => $summary->totalCents,
                'shipping_address_id' => $summary->shippingAddress->id,
                'billing_address_id' => $summary->billingAddress->id,
            ]);

            // Create order items from cart (snapshot)
            foreach ($cart as $cartItem) {
                // Resolve product by SKU
                $product = Product::whereSku($cartItem->sku)->firstOrFail();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'sku' => $cartItem->sku,
                    'name' => $cartItem->name,
                    'qty' => $cartItem->qty,
                    'price_cents' => $cartItem->price,
                    'total_cents' => $cartItem->getTotal(),
                    'options' => $cartItem->options,
                ]);
            }

            // Record initial status
            $order->recordStatusChange(OrderStatus::PENDING_PAYMENT, reason: 'Order created');

            // Fire event
            OrderCreated::dispatch($order);

            return $order;
        });
    }

    /**
     * Transition order to new status.
     *
     * Validates transition, updates status, records history, and fires event.
     * Stock is decremented when transitioning to PROCESSING.
     *
     * @param Order $order
     * @param OrderStatus $newStatus
     * @param User|null $user
     * @param string|null $reason
     * @return Order
     * @throws \InvalidArgumentException
     */
    public function transitionStatus(
        Order $order,
        OrderStatus $newStatus,
        ?User $user = null,
        ?string $reason = null,
    ): Order {
        // Validate transition
        if (!$order->canTransitionTo($newStatus)) {
            $allowed = implode(', ', array_map(
                fn ($s) => $s->label(),
                $order->getAllowedTransitions()
            ));

            throw new \InvalidArgumentException(
                "Cannot transition from {$order->status->label()} to {$newStatus->label()}. " .
                "Allowed: {$allowed}"
            );
        }

        // Apply side effects within transaction
        return DB::transaction(function () use ($order, $newStatus, $user, $reason) {
            // Decrement stock if transitioning to PROCESSING
            if ($newStatus === OrderStatus::PROCESSING) {
                $this->decrementStock($order);
            }

            // Update status and record history
            $order->recordStatusChange($newStatus, $user, $reason);
            $order->refresh();

            // Set timestamp fields
            match ($newStatus) {
                OrderStatus::SHIPPED => $order->update(['shipped_at' => now()]),
                OrderStatus::DELIVERED => $order->update(['delivered_at' => now()]),
                OrderStatus::COMPLETED => null, // delivered_at is enough
                default => null,
            };

            // Fire event
            OrderStatusChanged::dispatch($order, $newStatus);

            return $order;
        });
    }

    /**
     * Cancel an order.
     *
     * Only allowed for PENDING_PAYMENT or PROCESSING status.
     * Refunds stock if already decremented.
     *
     * @param Order $order
     * @param User|null $user
     * @param string|null $reason
     * @return Order
     * @throws \InvalidArgumentException
     */
    public function cancel(Order $order, ?User $user = null, ?string $reason = null): Order
    {
        if (!$order->canBeCancelled()) {
            throw new \InvalidArgumentException(
                "Order {$order->order_number} cannot be cancelled (status: {$order->status->label()})"
            );
        }

        return DB::transaction(function () use ($order, $user, $reason) {
            // Refund stock if it was decremented
            if ($order->status === OrderStatus::PROCESSING) {
                $this->refundStock($order);
            }

            // Update status and record history
            $order->recordStatusChange(OrderStatus::CANCELLED, $user, $reason);
            $order->update(['cancelled_at' => now()]);
            $order->refresh();

            // Cancel associated payment if pending
            $order->payment?->markAsCancelled($reason ?: 'Order cancelled');

            // Fire event
            OrderCancelled::dispatch($order);

            return $order;
        });
    }

    /**
     * Decrement product stock for order items.
     *
     * Called when order transitions to PROCESSING.
     *
     * @param Order $order
     * @throws \RuntimeException
     */
    private function decrementStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;

            if (!$product) {
                throw new \RuntimeException("Product not found for item {$item->id}");
            }

            // Decrement stock
            $product->decrement('stock_quantity', $item->qty);

            // Log activity
            activity('stock_decremented')
                ->performedOn($product)
                ->withProperties([
                    'order_id' => $order->id,
                    'qty' => $item->qty,
                    'new_stock' => $product->stock_quantity - $item->qty,
                ])
                ->log("Stock décrémenté pour commande {$order->order_number}");
        }
    }

    /**
     * Refund product stock for order items.
     *
     * Called when order is cancelled from PROCESSING.
     *
     * @param Order $order
     */
    private function refundStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;

            if (!$product) {
                continue;
            }

            // Refund stock
            $product->increment('stock_quantity', $item->qty);

            // Log activity
            activity('stock_refunded')
                ->performedOn($product)
                ->withProperties([
                    'order_id' => $order->id,
                    'qty' => $item->qty,
                    'new_stock' => $product->stock_quantity + $item->qty,
                ])
                ->log("Stock remboursé pour annulation {$order->order_number}");
        }
    }
}
