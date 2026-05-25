<?php

declare(strict_types=1);

namespace App\Domains\Order\Listeners;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Events\OrderStatusChanged;

class SendOrderStatusNotification
{
    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        // TODO: Implement in next phase (email notifications)
        // Notify customer based on new status
        match ($event->newStatus) {
            OrderStatus::PROCESSING => $this->notifyProcessing($event->order),
            OrderStatus::SHIPPED => $this->notifyShipped($event->order),
            OrderStatus::DELIVERED => $this->notifyDelivered($event->order),
            OrderStatus::COMPLETED => $this->notifyCompleted($event->order),
            default => null,
        };
    }

    private function notifyProcessing($order): void
    {
        // Mail::queue(new OrderProcessing($order));
    }

    private function notifyShipped($order): void
    {
        // Mail::queue(new OrderShipped($order));
    }

    private function notifyDelivered($order): void
    {
        // Mail::queue(new OrderDelivered($order));
    }

    private function notifyCompleted($order): void
    {
        // Mail::queue(new OrderCompleted($order));
    }
}
