<?php

declare(strict_types=1);

namespace App\Domains\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class CartMerged
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(
        public readonly int $userId,
        public readonly int $itemsMerged,
    ) {}
}
