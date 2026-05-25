<?php

declare(strict_types=1);

namespace App\Domains\Order\Listeners;

use App\Domains\Order\Events\OrderCreated;

class SendOrderConfirmationEmail
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        // TODO: Implement in next phase (email notifications)
        // Mailable will be queued to send order confirmation email
        // Mail::queue(new OrderConfirmation($event->order));
    }
}
