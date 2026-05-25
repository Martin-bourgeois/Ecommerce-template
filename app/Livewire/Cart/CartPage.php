<?php

declare(strict_types=1);

namespace App\Livewire\Cart;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class CartPage extends Component
{
    public array $items = [];
    public float $subtotal = 0;
    public float $tax = 0;
    public float $shipping = 0;
    public float $discount = 0;
    public float $total = 0;
    public string $couponCode = '';
    public bool $loading = false;
    public ?string $couponError = null;
    public ?string $couponMessage = null;

    public function mount(): void
    {
        $this->loadCart();
    }

    #[On('CartUpdated')]
    public function onCartUpdated(): void
    {
        $this->loadCart();
    }

    private function loadCart(): void
    {
        if (!Auth::check()) {
            $this->items = [];
            return;
        }

        $cartFacade = app(\App\Domains\Cart\Services\CartFacade::class);
        $cart = $cartFacade->getCart(Auth::user());

        $this->items = $cart->items()
            ->with('product')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_slug' => $item->product->slug,
                'product_image' => $item->product->getFirstMediaUrl('images') ?: $item->product->getFirstMediaUrl('thumbnail'),
                'quantity' => $item->quantity,
                'price' => ($item->product->discounted_price ?? $item->product->price),
                'total' => ($item->product->discounted_price ?? $item->product->price) * $item->quantity,
            ])
            ->toArray();

        $this->calculateTotals();
    }

    private function calculateTotals(): void
    {
        $this->subtotal = collect($this->items)->sum('total');
        $this->tax = $this->subtotal * 0.20; // 20% TVA
        $this->shipping = $this->subtotal > 100 ? 0 : 9.99; // Livraison gratuite > 100€
        $this->total = $this->subtotal + $this->tax + $this->shipping - $this->discount;
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->removeItem($itemId);
            return;
        }

        $cartFacade = app(\App\Domains\Cart\Services\CartFacade::class);
        $cartFacade->updateItemQuantity(Auth::user(), $itemId, $quantity);

        $this->dispatch('CartUpdated');
    }

    public function removeItem(int $itemId): void
    {
        $cartFacade = app(\App\Domains\Cart\Services\CartFacade::class);
        $cartFacade->removeItem(Auth::user(), $itemId);

        $this->dispatch('CartUpdated');
    }

    public function applyCoupon(): void
    {
        $this->couponError = null;
        $this->couponMessage = null;
        $this->loading = true;

        try {
            $promotionFacade = app(\App\Domains\Promotion\Services\PromotionFacade::class);
            $coupon = $promotionFacade->validateAndApplyCoupon(
                Auth::user(),
                $this->couponCode,
                $this->subtotal
            );

            $this->discount = $coupon->discount_value;
            $this->couponMessage = 'Coupon appliqué: -' . number_format($coupon->discount_value, 2) . '€';
            $this->couponCode = '';
            $this->calculateTotals();
        } catch (\Exception $e) {
            $this->couponError = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function checkout(): void
    {
        $this->redirectRoute('checkout.index');
    }

    public function render()
    {
        return view('livewire.cart.cart-page', [
            'items' => $this->items,
            'totals' => [
                'subtotal' => $this->subtotal,
                'tax' => $this->tax,
                'shipping' => $this->shipping,
                'discount' => $this->discount,
                'total' => $this->total,
            ],
        ]);
    }
}
