<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Domains\Catalog\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AddToCart extends Component
{
    public Product $product;
    public int $quantity = 1;
    public bool $adding = false;
    public ?string $message = null;

    public function incrementQuantity(): void
    {
        if ($this->quantity < $this->product->stock) {
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
        if (!Auth::check()) {
            $this->redirectRoute('login');
            return;
        }

        $this->adding = true;
        $this->message = null;

        try {
            $cartFacade = app(\App\Domains\Cart\Services\CartFacade::class);
            $cartFacade->addItem(Auth::user(), $this->product->id, $this->quantity);
            
            $this->dispatch('CartUpdated');
            $this->message = 'Ajouté au panier avec succès';
            $this->quantity = 1;
        } catch (\Exception $e) {
            Log::error('Cart add error: ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack: ' . $e->getTraceAsString());
            $this->message = 'Erreur lors de l\'ajout au panier';
        } finally {
            $this->adding = false;
        }
    }

    public function render()
    {
        return view('livewire.catalog.add-to-cart');
    }
}
