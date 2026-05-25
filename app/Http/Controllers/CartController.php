<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Cart\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function add(Request $request, CartService $cartService): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour ajouter au panier',
            ], 401);
        }

        $validated = $request->validate([
            'sku' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cartService->add($validated['sku'], $validated['quantity']);

            return response()->json([
                'success' => true,
                'message' => 'Produit ajouté au panier',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout au panier',
            ], 500);
        }
    }

    public function get(): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        $cartFacade = app(\App\Domains\Cart\Services\CartFacade::class);
        $cart = $cartFacade->getCart(Auth::user());

        return response()->json([
            'items' => $cart->items()->count(),
            'total' => $cart->getTotal(),
        ]);
    }
}
