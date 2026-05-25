<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Pwa\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    public function __construct(
        private PushNotificationService $pushService,
    ) {
    }

    /**
     * S'abonner aux notifications push
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        try {
            $subscription = $this->pushService->subscribe(
                Auth::user(),
                $validated['endpoint'],
                $validated['keys']
            );

            return response()->json([
                'success' => true,
                'message' => 'Souscription créée avec succès',
                'subscription' => $subscription,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Se désabonner des notifications push
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        try {
            $unsubscribed = $this->pushService->unsubscribe(
                Auth::user(),
                $validated['endpoint']
            );

            if (!$unsubscribed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Souscription non trouvée',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Désinscription réussie',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Récupérer les souscriptions de l'utilisateur
     */
    public function getSubscriptions(Request $request): JsonResponse
    {
        $subscriptions = \App\Domains\Pwa\Models\PushSubscription::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get(['id', 'endpoint', 'subscribed_at', 'last_used_at']);

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions,
            'count' => $subscriptions->count(),
        ]);
    }

    /**
     * Tester une notification push
     */
    public function testNotification(Request $request): JsonResponse
    {
        try {
            $sent = $this->pushService->send(
                Auth::user(),
                PushNotificationService::TYPE_ORDER_CONFIRMED,
                [
                    'order_id' => 1,
                    'order_number' => 'TEST-001',
                ]
            );

            if (!$sent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune souscription active',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification de test envoyée',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Endpoint pour les notifications en arrière-plan (sync)
     */
    public function getPending(Request $request): JsonResponse
    {
        $pending = $this->pushService->getPendingNotifications(Auth::user());

        return response()->json([
            'success' => true,
            'notifications' => $pending,
        ]);
    }

    /**
     * Gérer les actions de notification
     */
    public function handleAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string',
            'notification_id' => 'required|string',
        ]);

        // À implémenter selon les besoins
        // Exemples: marquer comme lu, supprimer, etc.

        return response()->json([
            'success' => true,
            'message' => 'Action effectuée',
        ]);
    }
}
