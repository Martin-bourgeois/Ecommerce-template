<?php

declare(strict_types=1);

namespace App\Domains\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class CartUpdated
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(
        public readonly ?string $guestSessionId,
        public readonly ?int $userId,
    ) {}
}
