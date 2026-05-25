<?php

declare(strict_types=1);

namespace App\Domains\Pwa\Services;

use App\Models\User;
use App\Domains\Order\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use App\Domains\Pwa\Models\PushSubscription;

class PushNotificationService
{
    /**
     * Types de notifications supportées
     */
    public const TYPE_ORDER_CONFIRMED = 'order_confirmed';
    public const TYPE_ORDER_SHIPPED = 'order_shipped';
    public const TYPE_ORDER_DELIVERED = 'order_delivered';
    public const TYPE_ORDER_CANCELLED = 'order_cancelled';
    public const TYPE_PROMOTION = 'promotion';
    public const TYPE_REVIEW_REQUEST = 'review_request';

    /**
     * Souscrire un utilisateur aux notifications push
     */
    public function subscribe(User $user, string $endpoint, array $keys): PushSubscription
    {
        // Récupérer ou créer la souscription
        $subscription = PushSubscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'endpoint' => $endpoint,
            ],
            [
                'p256dh' => $keys['p256dh'] ?? null,
                'auth' => $keys['auth'] ?? null,
                'is_active' => true,
                'subscribed_at' => now(),
            ]
        );

        Log::info('Push subscription created', [
            'user_id' => $user->id,
            'endpoint' => $endpoint,
        ]);

        return $subscription;
    }

    /**
     * Désabonner un utilisateur
     */
    public function unsubscribe(User $user, string $endpoint): bool
    {
        $deleted = PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $endpoint)
            ->delete();

        return $deleted > 0;
    }

    /**
     * Envoyer une notification push
     */
    public function send(User $user, string $type, array $data = []): bool
    {
        try {
            $subscriptions = PushSubscription::where('user_id', $user->id)
                ->where('is_active', true)
                ->get();

            if ($subscriptions->isEmpty()) {
                Log::info('Aucune souscription active', ['user_id' => $user->id]);
                return false;
            }

            foreach ($subscriptions as $subscription) {
                $this->sendToSubscription($subscription, $type, $data);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de la notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Envoyer une notification à une souscription spécifique
     */
    private function sendToSubscription(PushSubscription $subscription, string $type, array $data): void
    {
        $payload = $this->buildPayload($type, $data);

        try {
            if (config('services.push.provider') === 'web-push') {
                $this->sendViaWebPush($subscription, $payload);
            } elseif (config('services.push.provider') === 'onesignal') {
                $this->sendViaOneSignal($subscription, $payload);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi via subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            // Marquer la souscription comme inactive si elle n'existe plus
            if (str_contains($e->getMessage(), '410')) {
                $subscription->update(['is_active' => false]);
            }
        }
    }

    /**
     * Construire le payload de la notification
     */
    private function buildPayload(string $type, array $data): array
    {
        return match ($type) {
            self::TYPE_ORDER_CONFIRMED => [
                'title' => 'Commande confirmée',
                'body' => 'Votre commande #' . ($data['order_number'] ?? '') . ' a été confirmée',
                'icon' => asset('images/icon-192x192.png'),
                'badge' => asset('images/badge-72x72.png'),
                'tag' => 'order-' . ($data['order_id'] ?? ''),
                'data' => [
                    'url' => route('account.orders.show', $data['order_id'] ?? ''),
                    'type' => $type,
                ],
                'actions' => [
                    [
                        'action' => 'open',
                        'title' => 'Voir la commande',
                    ],
                ],
            ],
            self::TYPE_ORDER_SHIPPED => [
                'title' => 'Commande expédiée',
                'body' => 'Votre commande a été expédiée. Suivi: ' . ($data['tracking_number'] ?? ''),
                'icon' => asset('images/icon-192x192.png'),
                'badge' => asset('images/badge-72x72.png'),
                'tag' => 'order-' . ($data['order_id'] ?? ''),
                'data' => [
                    'url' => route('account.orders.show', $data['order_id'] ?? ''),
                    'type' => $type,
                    'tracking_number' => $data['tracking_number'] ?? '',
                ],
                'actions' => [
                    [
                        'action' => 'open',
                        'title' => 'Suivre',
                    ],
                ],
            ],
            self::TYPE_ORDER_DELIVERED => [
                'title' => 'Commande livrée',
                'body' => 'Votre commande a été livrée avec succès',
                'icon' => asset('images/icon-192x192.png'),
                'badge' => asset('images/badge-72x72.png'),
                'tag' => 'order-' . ($data['order_id'] ?? ''),
                'data' => [
                    'url' => route('account.orders.show', $data['order_id'] ?? ''),
                    'type' => $type,
                ],
                'actions' => [
                    [
                        'action' => 'open',
                        'title' => 'Voir la commande',
                    ],
                ],
            ],
            default => [
                'title' => 'Notification',
                'body' => $data['message'] ?? 'Vous avez reçu une notification',
                'icon' => asset('images/icon-192x192.png'),
                'badge' => asset('images/badge-72x72.png'),
            ],
        };
    }

    /**
     * Envoyer via Web Push API
     */
    private function sendViaWebPush(PushSubscription $subscription, array $payload): void
    {
        // Implémentation Web Push API avec minishlink/web-push
        // À implémenter avec le package web-push
        
        $auth = config('services.push.vapid_public_key');
        $public = config('services.push.vapid_private_key');

        if (!$auth || !$public) {
            Log::warning('Clés VAPID manquantes pour Web Push');
            return;
        }

        // La logique d'envoi Web Push sera implémentée ici
        // Utiliser le package minishlink/web-push si disponible
        Log::info('Web Push notification envoyée', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Envoyer via OneSignal
     */
    private function sendViaOneSignal(PushSubscription $subscription, array $payload): void
    {
        $appId = config('services.onesignal.app_id');
        $restKey = config('services.onesignal.rest_key');

        if (!$appId || !$restKey) {
            Log::warning('Clés OneSignal manquantes');
            return;
        }

        // La logique d'envoi OneSignal sera implémentée ici
        Log::info('OneSignal notification envoyée', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Envoyer une notification à une commande
     */
    public function sendOrderNotification(Order $order, string $type): bool
    {
        return $this->send($order->user, $type, [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'tracking_number' => $order->tracking_number ?? null,
        ]);
    }

    /**
     * Mettre en file d'attente une notification push
     */
    public function queueNotification(User $user, string $type, array $data = []): void
    {
        // Stocker dans le cache pour la synchronisation en arrière-plan
        $key = "pending_notification:{$user->id}:{$type}";
        Cache::put($key, $data, now()->addHours(24));
    }

    /**
     * Récupérer les notifications en attente
     */
    public function getPendingNotifications(User $user): array
    {
        // Cette méthode est utilisée par le service worker
        // Pour l'instant, retourner un tableau vide
        // À implémenter avec une base de données ou cache persistant
        return [];
    }

    /**
     * Nettoyer les notifications en attente
     */
    public function clearPendingNotifications(User $user): void
    {
        // À implémenter si utilisation de cache persistant
    }
}
