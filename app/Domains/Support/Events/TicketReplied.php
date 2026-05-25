<?php

declare(strict_types=1);

namespace App\Domains\Support\Events;

use App\Domains\Support\Models\TicketMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplied
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TicketMessage $message
    ) {
    }
}
