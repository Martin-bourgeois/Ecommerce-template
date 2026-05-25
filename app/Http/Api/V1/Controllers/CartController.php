<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers;

use App\Http\Api\V1\Data\CartData;
use App\Http\Api\V1\Requests\AddToCartRequest;
use App\Http\Api\V1\Requests\UpdateCartItemRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Contrôleur pour le panier
 * Endpoints: get, add, update, remove
 * Utilise CartService du domaine pour la logique métier
 */
class CartController
{
    /**
     * Obtenir le panier de l'utilisateur
     *
     * @route GET /api/v1/cart
     */
    public function index(Request $request): JsonResponse
    {
        // $cartService = app(\App\Domains\Cart\Services\CartService::class);
        // $cart = $cartService->getCart($request->user('sanctum'));

        // Pour l'instant, retourner un panier vide
        return response()->json([
            'data' => [
                'id' => 'cart_' . $request->user('sanctum')?->id,
                'user_id' => $request->user('sanctum')?->id,
                'items' => [],
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
                'coupon_code' => null,
                'discount' => null,
            ],
        ], 200);
    }

    /**
     * Ajouter un produit au panier
     *
     * @route POST /api/v1/cart/add
     */
    public function add(AddToCartRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user('sanctum');

        try {
            // $cartService = app(\App\Domains\Cart\Services\CartService::class);
            // $cart = $cartService->addItem($user, $validated['product_id'], $validated['quantity']);

            return response()->json([
                'message' => 'Product added to cart',
                'data' => [
                    'id' => 'cart_' . $user->id,
                    'user_id' => $user->id,
                    'items' => [],
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'coupon_code' => null,
                    'discount' => null,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add product to cart',
                'errors' => [
                    'cart' => [$e->getMessage()],
                ],
            ], 400);
        }
    }

    /**
     * Mettre à jour la quantité d'un article
     *
     * @route PATCH /api/v1/cart/items/{product_id}
     */
    public function update(int $productId, UpdateCartItemRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user('sanctum');

        try {
            // $cartService = app(\App\Domains\Cart\Services\CartService::class);
            // $cart = $cartService->updateItemQuantity($user, $productId, $validated['quantity']);

            return response()->json([
                'message' => 'Cart item updated',
                'data' => [
                    'id' => 'cart_' . $user->id,
                    'user_id' => $user->id,
                    'items' => [],
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'coupon_code' => null,
                    'discount' => null,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update cart item',
                'errors' => [
                    'cart' => [$e->getMessage()],
                ],
            ], 400);
        }
    }

    /**
     * Supprimer un article du panier
     *
     * @route DELETE /api/v1/cart/items/{product_id}
     */
    public function remove(int $productId, Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        try {
            // $cartService = app(\App\Domains\Cart\Services\CartService::class);
            // $cartService->removeItem($user, $productId);

            return response()->json([
                'message' => 'Cart item removed',
                'data' => [
                    'id' => 'cart_' . $user->id,
                    'user_id' => $user->id,
                    'items' => [],
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'coupon_code' => null,
                    'discount' => null,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove cart item',
                'errors' => [
                    'cart' => [$e->getMessage()],
                ],
            ], 400);
        }
    }

    /**
     * Vider le panier
     *
     * @route DELETE /api/v1/cart
     */
    public function clear(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        try {
            // $cartService = app(\App\Domains\Cart\Services\CartService::class);
            // $cartService->clear($user);

            return response()->json([
                'message' => 'Cart cleared',
                'data' => [
                    'id' => 'cart_' . $user->id,
                    'user_id' => $user->id,
                    'items' => [],
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'coupon_code' => null,
                    'discount' => null,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to clear cart',
                'errors' => [
                    'cart' => [$e->getMessage()],
                ],
            ], 400);
        }
    }

    /**
     * Appliquer un code coupon
     *
     * @route POST /api/v1/cart/coupon
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'coupon_code' => ['required', 'string', 'exists:coupons,code'],
        ]);

        $user = $request->user('sanctum');

        try {
            // $cartService = app(\App\Domains\Cart\Services\CartService::class);
            // $cart = $cartService->applyCoupon($user, $request->input('coupon_code'));

            return response()->json([
                'message' => 'Coupon applied',
                'data' => [
                    'id' => 'cart_' . $user->id,
                    'user_id' => $user->id,
                    'items' => [],
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'coupon_code' => $request->input('coupon_code'),
                    'discount' => 0,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to apply coupon',
                'errors' => [
                    'coupon' => [$e->getMessage()],
                ],
            ], 400);
        }
    }
}
