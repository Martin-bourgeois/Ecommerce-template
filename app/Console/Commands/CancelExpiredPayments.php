<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Order\Services\PayPalPaymentService;
use Illuminate\Console\Command;

class CancelExpiredPayments extends Command
{
    protected $signature = 'payments:cancel-expired';

    protected $description = 'Cancel payments that are pending for more than 48 hours';

    public function handle(): int
    {
        $service = app(PayPalPaymentService::class);
        $count = $service->cancelExpired();

        if ($count > 0) {
            $this->info("Cancelled {$count} expired payment(s)");
        } else {
            $this->info('No expired payments found');
        }

        return 0;
    }
}
