<?php

declare(strict_types=1);

namespace App\Domains\Order\Events;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Order $order,
        public OrderStatus $newStatus,
    ) {
    }
}
