<?php

declare(strict_types=1);

namespace App\Domains\Cart\Services;

use App\Domains\Cart\Models\Cart;
use App\Domains\Catalog\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Facade de haut niveau pour les opérations panier utilisateur.
 * Adapte l'interface du CartService Redis aux besoins des composants Livewire.
 */
class CartFacade
{
    public function __construct(
        private CartService $cartService,
    ) {}

    /**
     * Récupère ou crée le panier utilisateur (modèle database).
     */
    public function getCart(User $user): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['total_price' => 0, 'total_items' => 0]
        );
    }

    /**
     * Ajoute un produit au panier de l'utilisateur.
     */
    public function addItem(User $user, int $productId, int $quantity): void
    {
        $product = Product::findOrFail($productId);
        
        if ($product->stock < $quantity) {
            throw new \InvalidArgumentException('Stock insuffisant pour ce produit');
        }

        $cart = $this->getCart($user);
        $existingItem = $cart->items()
            ->where('product_id', $productId)
            ->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if ($product->stock < $newQuantity) {
                throw new \InvalidArgumentException('Stock insuffisant pour cette quantité');
            }
            $existingItem->update(['quantity' => $newQuantity]);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'price_cents' => (int) ($product->price * 100),
            ]);
        }

        $this->updateCartTotals($cart);
        event(new \App\Domains\Cart\Events\CartUpdated(null, $user->id));
    }

    /**
     * Met à jour la quantité d'un article du panier.
     */
    public function updateItemQuantity(User $user, int $cartItemId, int $quantity): void
    {
        $cart = $this->getCart($user);
        $item = $cart->items()->findOrFail($cartItemId);
        
        if ($quantity <= 0) {
            $item->delete();
        } else {
            if ($item->product->stock < $quantity) {
                throw new \InvalidArgumentException('Stock insuffisant');
            }
            $item->update(['quantity' => $quantity]);
        }

        $this->updateCartTotals($cart);
        event(new \App\Domains\Cart\Events\CartUpdated(null, $user->id));
    }

    /**
     * Supprime un article du panier.
     */
    public function removeItem(User $user, int $cartItemId): void
    {
        $cart = $this->getCart($user);
        $cart->items()->findOrFail($cartItemId)->delete();

        $this->updateCartTotals($cart);
        event(new \App\Domains\Cart\Events\CartUpdated(null, $user->id));
    }

    /**
     * Vide le panier complètement.
     */
    public function clearCart(User $user): void
    {
        $cart = $this->getCart($user);
        $cart->items()->delete();
        $cart->update(['total_price' => 0, 'total_items' => 0]);

        event(new \App\Domains\Cart\Events\CartUpdated(null, $user->id));
    }

    /**
     * Récalcule les totaux du panier.
     */
    private function updateCartTotals(Cart $cart): void
    {
        $items = $cart->items()->with('product')->get();
        
        $totalPrice = $items->sum(fn($item) => 
            $item->quantity * ($item->product->discounted_price ?? $item->product->price)
        );
        
        $totalItems = $items->sum('quantity');

        $cart->update([
            'total_price' => $totalPrice,
            'total_items' => $totalItems,
        ]);
    }
}
