<?php

declare(strict_types=1);

namespace App\Domains\Order\Listeners;

use App\Domains\Order\Events\OrderCancelled;

class SendOrderCancelledNotification
{
    /**
     * Handle the event.
     */
    public function handle(OrderCancelled $event): void
    {
        // TODO: Implement in next phase (email notifications)
        // Notify customer that order has been cancelled
        // Mail::queue(new OrderCancelled($event->order));
    }
}
