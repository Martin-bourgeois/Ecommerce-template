<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers;

use App\Http\Api\V1\Data\OrderData;
use App\Http\Api\V1\Requests\CreateOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Contrôleur pour les commandes
 * Endpoints: historique, détail, création
 */
class OrderController
{
    /**
     * Obtenir l'historique des commandes de l'utilisateur
     *
     * @route GET /api/v1/orders
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');
        
        $request->validate([
            'status' => ['nullable', 'string', 'in:pending,processing,shipped,delivered,cancelled'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        // $orders = $user->orders()
        //     ->when($request->input('status'), fn($q) => $q->where('status', $request->input('status')))
        //     ->orderBy('created_at', 'desc')
        //     ->paginate($request->input('per_page', 15));

        // Pour l'instant, retourner une liste vide
        return response()->json([
            'data' => [],
            'pagination' => [
                'total' => 0,
                'per_page' => 15,
                'current_page' => 1,
                'last_page' => 1,
            ],
        ], 200);
    }

    /**
     * Obtenir les détails d'une commande
     *
     * @route GET /api/v1/orders/{order}
     */
    public function show(int $orderId, Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        try {
            // $order = $user->orders()->findOrFail($orderId);

            return response()->json([
                'data' => [
                    'id' => $orderId,
                    'order_number' => 'ORD-' . (string) str_pad((string) $orderId, 6, '0', STR_PAD_LEFT),
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'items' => [],
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'discount' => null,
                    'payment_method' => 'card',
                    'payment_status' => 'pending',
                    'shipping_address' => [],
                    'tracking_number' => null,
                    'carrier' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'shipped_at' => null,
                    'delivered_at' => null,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order not found',
                'errors' => [
                    'order' => ['La commande n\'existe pas'],
                ],
            ], 404);
        }
    }

    /**
     * Créer une nouvelle commande
     *
     * @route POST /api/v1/orders
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user('sanctum');

        try {
            // Vérifier que le panier n'est pas vide
            // $cartService = app(\App\Domains\Cart\Services\CartService::class);
            // $cart = $cartService->getCart($user);
            // if (empty($cart->items)) {
            //     return response()->json([
            //         'message' => 'Cart is empty',
            //         'errors' => [
            //             'cart' => ['Le panier est vide'],
            //         ],
            //     ], 422);
            // }

            // // Créer la commande
            // $orderService = app(\App\Domains\Order\Services\OrderService::class);
            // $order = $orderService->createOrder($user, $validated);

            return response()->json([
                'message' => 'Order created successfully',
                'data' => [
                    'id' => 1,
                    'order_number' => 'ORD-000001',
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'items' => [],
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'discount' => null,
                    'payment_method' => 'card',
                    'payment_status' => 'pending',
                    'shipping_address' => [],
                    'tracking_number' => null,
                    'carrier' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'shipped_at' => null,
                    'delivered_at' => null,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order',
                'errors' => [
                    'order' => [$e->getMessage()],
                ],
            ], 400);
        }
    }

    /**
     * Annuler une commande
     *
     * @route POST /api/v1/orders/{order}/cancel
     */
    public function cancel(int $orderId, Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        try {
            // $order = $user->orders()->findOrFail($orderId);
            // $orderService = app(\App\Domains\Order\Services\OrderService::class);
            // $orderService->cancel($order);

            return response()->json([
                'message' => 'Order cancelled successfully',
                'data' => [
                    'id' => $orderId,
                    'status' => 'cancelled',
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel order',
                'errors' => [
                    'order' => [$e->getMessage()],
                ],
            ], 400);
        }
    }

    /**
     * Obtenir les détails du suivi (tracking)
     *
     * @route GET /api/v1/orders/{order}/tracking
     */
    public function tracking(int $orderId, Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        try {
            // $order = $user->orders()->findOrFail($orderId);

            return response()->json([
                'data' => [
                    'order_id' => $orderId,
                    'status' => 'shipped',
                    'tracking_number' => 'TRACK-123456',
                    'carrier' => 'FedEx',
                    'estimated_delivery' => now()->addDays(5),
                    'timeline' => [
                        [
                            'status' => 'Order confirmed',
                            'date' => now()->subDays(3),
                            'location' => 'Warehouse',
                        ],
                        [
                            'status' => 'Shipped',
                            'date' => now()->subDays(2),
                            'location' => 'Distribution Center',
                        ],
                        [
                            'status' => 'In transit',
                            'date' => now()->subDays(1),
                            'location' => 'Regional Hub',
                        ],
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order not found',
                'errors' => [
                    'order' => ['La commande n\'existe pas'],
                ],
            ], 404);
        }
    }
}
