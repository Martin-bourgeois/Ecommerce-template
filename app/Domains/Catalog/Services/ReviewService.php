<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Models\Review;
use App\Domains\Catalog\Models\ReviewHelpful;
use App\Domains\Catalog\Models\Product;
use App\Domains\Order\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * Create a new review for a product from an order.
     *
     * @throws ModelNotFoundException
     */
    public function createReview(
        Order $order,
        Product $product,
        User $user,
        array $data
    ): Review {
        // Verify purchase: product must be in order
        $orderItem = $order->items()
            ->where('product_id', $product->id)
            ->first();

        if (!$orderItem) {
            throw new ModelNotFoundException('Product not found in this order');
        }

        // Verify order is delivered
        if (!$order->isDelivered()) {
            throw new \RuntimeException('Order must be delivered before leaving a review');
        }

        // Check if review already exists
        $existingReview = Review::where('product_id', $product->id)
            ->where('order_id', $order->id)
            ->first();

        if ($existingReview) {
            throw new \RuntimeException('Review already exists for this product in this order');
        }

        // Validate comment length
        if (strlen($data['comment'] ?? '') < 20) {
            throw new \RuntimeException('Comment must be at least 20 characters');
        }

        // Create review
        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'rating' => (int) $data['rating'],
            'title' => $data['title'],
            'comment' => $data['comment'],
            'verified_purchase' => true,
            // Auto-approve if rating >= 4
            'is_approved' => (int) $data['rating'] >= 4,
        ]);

        // Handle media uploads
        if (!empty($data['photos'])) {
            foreach (array_slice($data['photos'], 0, 3) as $photo) {
                $review->addMedia($photo)
                    ->toMediaCollection('photos', 'public');
            }
        }

        return $review;
    }

    /**
     * Approve a review.
     */
    public function approve(Review $review): Review
    {
        $review->update(['is_approved' => true]);
        return $review->refresh();
    }

    /**
     * Reject a review (soft delete).
     */
    public function reject(Review $review): void
    {
        $review->delete();
    }

    /**
     * Mark review as helpful by user.
     * Prevents duplicate votes.
     */
    public function markHelpful(Review $review, User $user): bool
    {
        // Check if already marked
        $exists = ReviewHelpful::where('review_id', $review->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return false;
        }

        // Create helpful vote and increment counter
        DB::transaction(function () use ($review, $user) {
            ReviewHelpful::create([
                'review_id' => $review->id,
                'user_id' => $user->id,
            ]);

            $review->increment('helpful_count');
        });

        return true;
    }

    /**
     * Unmark review as helpful by user.
     */
    public function unmarkHelpful(Review $review, User $user): bool
    {
        $deleted = ReviewHelpful::where('review_id', $review->id)
            ->where('user_id', $user->id)
            ->delete();

        if ($deleted > 0) {
            $review->decrement('helpful_count');
            return true;
        }

        return false;
    }

    /**
     * Get average rating for a product.
     */
    public function getAverageRating(Product $product): float
    {
        return Review::where('product_id', $product->id)
            ->approved()
            ->average('rating') ?? 0;
    }

    /**
     * Get rating distribution for a product.
     */
    public function getRatingDistribution(Product $product): array
    {
        $distribution = [];

        for ($i = 1; $i <= 5; $i++) {
            $count = Review::where('product_id', $product->id)
                ->approved()
                ->where('rating', $i)
                ->count();

            $distribution[$i] = $count;
        }

        return $distribution;
    }

    /**
     * Get total approved reviews count for a product.
     */
    public function getReviewsCount(Product $product): int
    {
        return Review::where('product_id', $product->id)
            ->approved()
            ->count();
    }

    /**
     * Get pending reviews count (for admin).
     */
    public function getPendingCount(): int
    {
        return Review::pending()->count();
    }
}
