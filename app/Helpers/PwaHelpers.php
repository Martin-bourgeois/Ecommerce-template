<?php

declare(strict_types=1);

/**
 * PWA Helper Functions
 * 
 * Add these helpers to your application
 */

use App\Domains\Pwa\Services\PushNotificationService;
use App\Models\User;

/**
 * Envoyer une notification push de commande
 */
if (!function_exists('send_order_notification')) {
    function send_order_notification(
        \App\Domains\Order\Models\Order $order,
        string $type = PushNotificationService::TYPE_ORDER_CONFIRMED
    ): bool {
        try {
            $service = app(PushNotificationService::class);
            return $service->sendOrderNotification($order, $type);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur notification commande', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}

/**
 * Envoyer une notification push personnalisée
 */
if (!function_exists('send_push_notification')) {
    function send_push_notification(
        User $user,
        string $type,
        array $data = []
    ): bool {
        try {
            $service = app(PushNotificationService::class);
            return $service->send($user, $type, $data);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur envoi notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}

/**
 * Souscrire un utilisateur aux notifications push
 */
if (!function_exists('subscribe_to_push')) {
    function subscribe_to_push(
        User $user,
        string $endpoint,
        array $keys
    ): \App\Domains\Pwa\Models\PushSubscription {
        $service = app(PushNotificationService::class);
        return $service->subscribe($user, $endpoint, $keys);
    }
}

/**
 * Obtenir le nombre de souscriptions actives d'un utilisateur
 */
if (!function_exists('user_push_subscriptions_count')) {
    function user_push_subscriptions_count(User $user): int
    {
        return \App\Domains\Pwa\Models\PushSubscription::where('user_id', $user->id)
            ->where('is_active', true)
            ->count();
    }
}

/**
 * Vérifier si la PWA est activée
 */
if (!function_exists('is_pwa_enabled')) {
    function is_pwa_enabled(): bool
    {
        return (bool) config('pwa.background_sync.enabled', true);
    }
}

/**
 * Obtenir la version du cache
 */
if (!function_exists('pwa_cache_version')) {
    function pwa_cache_version(): string
    {
        return (string) (config('pwa.cache.version') ?? 'v1');
    }
}

/**
 * Obtenir la couleur du thème
 */
if (!function_exists('pwa_theme_color')) {
    function pwa_theme_color(): string
    {
        return (string) (config('pwa.theme_color') ?? '#3b82f6');
    }
}

/**
 * Obtenir le nom de l'app PWA
 */
if (!function_exists('pwa_app_name')) {
    function pwa_app_name(): string
    {
        return (string) (config('pwa.name') ?? config('app.name'));
    }
}

/**
 * Obtenir le nom court de l'app
 */
if (!function_exists('pwa_short_name')) {
    function pwa_short_name(): string
    {
        return (string) (config('pwa.short_name') ?? 'App');
    }
}
