<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Events;

use App\Domains\Catalog\Models\Review;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Review $review)
    {
    }
}
