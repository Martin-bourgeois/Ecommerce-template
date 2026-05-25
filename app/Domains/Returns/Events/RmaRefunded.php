<?php

declare(strict_types=1);

namespace App\Domains\Returns\Events;

use App\Domains\Returns\Models\Rma;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RmaRefunded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Rma $rma
    ) {
    }
}
