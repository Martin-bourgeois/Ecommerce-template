<?php

declare(strict_types=1);

namespace App\Domains\Order\Events;

use App\Domains\Order\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Payment $payment,
    ) {
    }
}
