<?php

declare(strict_types=1);

namespace App\Domains\Returns\Listeners;

use App\Domains\Returns\Events\RmaApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRmaApprovedNotification implements ShouldQueue
{
    public function handle(RmaApproved $event): void
    {
        $rma = $event->rma;
        $user = $rma->user;

        try {
            Mail::to($user)->send(new RmaApprovedMail($rma));
        } catch (\Exception $e) {
            Log::error("Failed to send RMA approved notification: " . $e->getMessage());
        }
    }
}

class RmaApprovedMail extends Mailable
{
    public function __construct(
        public $rma
    ) {
    }

    public function envelope()
    {
        return [
            'subject' => "Votre demande de retour {$this->rma->rma_number} a été approuvée",
        ];
    }

    public function content()
    {
        return [
            'view' => 'emails.returns.rma-approved',
            'with' => [
                'rma' => $this->rma,
            ],
        ];
    }
}
