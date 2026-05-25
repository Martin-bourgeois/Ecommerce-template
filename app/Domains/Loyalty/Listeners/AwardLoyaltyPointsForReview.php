<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\Listeners;

use App\Domains\Catalog\Events\ReviewSubmitted;
use App\Domains\Loyalty\Models\LoyaltyAccount;
use App\Domains\Loyalty\Models\LoyaltyTransaction;

class AwardLoyaltyPointsForReview
{
    /**
     * Handle the event.
     */
    public function handle(ReviewSubmitted $event): void
    {
        $review = $event->review;

        // Only award points if review is approved or auto-approved
        if (!$review->is_approved) {
            return;
        }

        $points = 0;

        // +10 points for review with photos
        if ($review->hasPhotos()) {
            $points += 10;
        }

        // +5 points for text review (always has text if created)
        if (!empty($review->comment)) {
            $points += 5;
        }

        // Award points if any
        if ($points > 0) {
            $loyaltyAccount = LoyaltyAccount::firstOrCreate(
                ['user_id' => $review->user_id],
                ['balance' => 0]
            );

            $loyaltyAccount->addPoints(
                points: $points,
                description: "Review bonus: {$points}pts for rating on {$review->product->name}",
                reference: "review_{$review->id}"
            );
        }
    }
}
