<?php

declare(strict_types=1);

namespace App\Domains\Returns\Listeners;

use App\Domains\Returns\Events\RmaRefunded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRmaRefundedNotification implements ShouldQueue
{
    public function handle(RmaRefunded $event): void
    {
        $rma = $event->rma;
        $user = $rma->user;

        try {
            Mail::to($user)->send(new RmaRefundedMail($rma));
        } catch (\Exception $e) {
            Log::error("Failed to send RMA refunded notification: " . $e->getMessage());
        }
    }
}

class RmaRefundedMail extends Mailable
{
    public function __construct(
        public $rma
    ) {
    }

    public function envelope()
    {
        return [
            'subject' => "Votre remboursement pour le retour {$this->rma->rma_number} a été traité",
        ];
    }

    public function content()
    {
        return [
            'view' => 'emails.returns.rma-refunded',
            'with' => [
                'rma' => $this->rma,
            ],
        ];
    }
}
