<?php

declare(strict_types=1);

namespace App\Livewire\Cart;

use App\Domains\Cart\Services\CartService;
use Livewire\Component;
use Livewire\Attributes\Validate;

class AddToCart extends Component
{
    public string $sku;
    public int $productId;

    #[Validate('required|numeric|min:1')]
    public int $quantity = 1;

    public bool $loading = false;
    public string $message = '';

    public function mount(string $sku, int $productId): void
    {
        $this->sku = $sku;
        $this->productId = $productId;
    }

    public function addToCart(CartService $cartService): void
    {
        $this->validate();
        $this->loading = true;

        try {
            $cartService->add($this->sku, $this->quantity);

            $this->message = sprintf(
                '%d article(s) ajouté(s) au panier',
                $this->quantity
            );

            $this->dispatch('notify', message: $this->message, type: 'success');
            $this->dispatch('cart:updated');

            // Reset form
            $this->quantity = 1;
        } catch (\InvalidArgumentException $e) {
            $this->message = $e->getMessage();
            $this->dispatch('notify', message: $this->message, type: 'error');
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.cart.add-to-cart');
    }
}
