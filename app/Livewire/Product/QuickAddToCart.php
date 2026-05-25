<?php

declare(strict_types=1);

namespace App\Livewire\Product;

use App\Domains\Catalog\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class QuickAddToCart extends Component
{
    public int $productId;
    public bool $adding = false;
    public ?string $message = null;
    public bool $showMessage = false;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    public function addToCart(): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('login');
            return;
        }

        $this->adding = true;
        $this->message = null;
        $this->showMessage = false;

        try {
            $cartFacade = app(\App\Domains\Cart\Services\CartFacade::class);
            $cartFacade->addItem(Auth::user(), $this->productId, 1);
            
            $this->dispatch('cart:updated');
            $this->message = '✓ Ajouté au panier';
            $this->showMessage = true;
            
            // Cache le message après 3 secondes
            $this->js('setTimeout(() => { $wire.hideMessage() }, 3000)');
        } catch (\Exception $e) {
            Log::error('Cart add error: ' . $e->getMessage());
            $this->message = '✗ Erreur lors de l\'ajout';
            $this->showMessage = true;
        } finally {
            $this->adding = false;
        }
    }

    public function hideMessage(): void
    {
        $this->showMessage = false;
    }

    public function render()
    {
        return view('livewire.product.quick-add-to-cart');
    }
}
