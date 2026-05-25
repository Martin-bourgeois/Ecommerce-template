<?php

declare(strict_types=1);

namespace App\Livewire\Cart;

use App\Domains\Cart\Services\CartFacade;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class MiniCart extends Component
{
    public array $items = [];
    public float $total = 0;

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
            $this->total = 0;
            return;
        }

        $cartFacade = app(CartFacade::class);
        $cart = $cartFacade->getCart(Auth::user());

        $this->items = $cart->items()
            ->with('product')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_image' => $item->product->getFirstMediaUrl('images') ?: $item->product->getFirstMediaUrl('thumbnail'),
                'quantity' => $item->quantity,
                'price_cents' => $item->price_cents,
                'price' => $item->price_cents / 100,
                'total' => ($item->price_cents / 100) * $item->quantity,
            ])
            ->toArray();

        $this->total = collect($this->items)->sum('total');
    }

    public function removeItem(int $itemId): void
    {
        $cartFacade = app(CartFacade::class);
        $cartFacade->removeItem(Auth::user(), $itemId);
        
        $this->dispatch('CartUpdated');
    }

    public function render()
    {
        return view('livewire.cart.mini-cart', [
            'items' => $this->items,
            'total' => $this->total,
            'cartCount' => count($this->items),
        ]);
    }
}
