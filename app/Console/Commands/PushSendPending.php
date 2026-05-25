<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Order\Models\Order;
use App\Domains\Pwa\Services\PushNotificationService;

class PushSendPending extends Command
{
    protected $signature = 'push:send-pending 
                            {--order-id= : ID de la commande à notifier}
                            {--type=order_confirmed : Type de notification}
                            {--limit=100 : Limite de notifications à traiter}';

    protected $description = 'Envoyer les notifications push en attente';

    public function handle(): int
    {
        $service = app(PushNotificationService::class);

        if ($this->option('order-id')) {
            // Envoyer une notification pour une commande spécifique
            return $this->sendOrderNotification($service);
        }

        // Envoyer toutes les notifications en attente
        return $this->sendPendingNotifications($service);
    }

    private function sendOrderNotification(PushNotificationService $service): int
    {
        $orderId = $this->option('order-id');
        $type = $this->option('type');

        $order = Order::find($orderId);

        if (!$order) {
            $this->error("Commande #{$orderId} non trouvée");
            return 1;
        }

        $this->info("Envoi de notification pour la commande #{$order->order_number}...");

        try {
            $sent = $service->sendOrderNotification($order, $type);

            if ($sent) {
                $this->info("✓ Notification envoyée à {$order->user->name}");
                return 0;
            } else {
                $this->warn("✗ Aucune souscription active pour {$order->user->name}");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Erreur: {$e->getMessage()}");
            return 1;
        }
    }

    private function sendPendingNotifications(PushNotificationService $service): int
    {
        $limit = (int)$this->option('limit');
        $bar = $this->output->createProgressBar($limit);
        $bar->start();

        // Récupérer les commandes récemment mises à jour
        $orders = Order::where('updated_at', '>=', now()->subHours(24))
            ->limit($limit)
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($orders as $order) {
            try {
                if ($service->sendOrderNotification($order, PushNotificationService::TYPE_ORDER_CONFIRMED)) {
                    $sent++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("✓ {$sent} notification(s) envoyée(s)");
        if ($failed > 0) {
            $this->warn("✗ {$failed} notification(s) échouée(s)");
        }

        return 0;
    }
}
