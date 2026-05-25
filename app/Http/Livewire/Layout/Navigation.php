<?php

declare(strict_types=1);

namespace App\Http\Livewire\Layout;

use App\Domains\Catalog\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class Navigation extends Component
{
    public array $categories = [];
    public int $cartCount = 0;
    public bool $searchOpen = false;

    public function mount(): void
    {
        $this->loadCategories();
        $this->updateCartCount();
    }

    #[On('CartUpdated')]
    public function onCartUpdated(): void
    {
        $this->updateCartCount();
    }

    private function loadCategories(): void
    {
        $this->categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->toArray();
    }

    private function updateCartCount(): void
    {
        if (Auth::check()) {
            // Récupérer du service cart
            $cartFacade = app(\App\Domains\Cart\Services\CartFacade::class);
            $cart = $cartFacade->getCart(Auth::user());
            $this->cartCount = $cart->items()->count() ?? 0;
        } else {
            $this->cartCount = 0;
        }
    }

    public function render()
    {
        return view('livewire.layout.navigation', [
            'categories' => $this->categories,
            'cartCount' => $this->cartCount,
        ]);
    }
}
