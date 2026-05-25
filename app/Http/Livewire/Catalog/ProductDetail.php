<?php

declare(strict_types=1);

namespace App\Http\Livewire\Catalog;

use App\Domains\Catalog\Models\Product;
use Livewire\Component;

class ProductDetail extends Component
{
    public Product $product;
    public int $quantity = 1;
    public string $selectedAttributes = '';
    public bool $adding = false;
    public ?string $message = null;

    public function mount(Product $product): void
    {
        $this->product = $product->load([
            'categories',
            'variants',
            'images',
            'reviews' => function ($q) {
                $q->where('approved', true)->latest()->limit(5);
            },
        ]);
    }

    public function incrementQuantity(): void
    {
        $available = $this->product->stock;
        if ($this->quantity < $available) {
            $this->quantity++;
        }
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart(): void
    {
        $this->adding = true;
        $this->message = null;

        try {
            if (\Illuminate\Support\Facades\Auth::guest()) {
                $this->redirectRoute('login');
                return;
            }

            if ($this->product->stock < $this->quantity) {
                $this->message = 'Stock insuffisant';
                return;
            }

            $cartFacade = app(\App\Domains\Cart\Services\CartFacade::class);
            $cartFacade->addItem(
                \Illuminate\Support\Facades\Auth::user(),
                $this->product->id,
                $this->quantity
            );

            $this->dispatch('CartUpdated');
            $this->message = 'Ajouté au panier!';
            $this->quantity = 1;
        } catch (\Exception $e) {
            $this->message = 'Erreur: ' . $e->getMessage();
        } finally {
            $this->adding = false;
        }
    }

    public function render()
    {
        return view('livewire.catalog.product-detail', [
            'product' => $this->product,
        ]);
    }
}
