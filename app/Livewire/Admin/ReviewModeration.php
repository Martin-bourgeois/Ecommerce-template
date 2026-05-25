<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Domains\Catalog\Models\Review;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewModeration extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public function approve(Review $review): void
    {
        $service = app(\App\Domains\Catalog\Services\ReviewService::class);
        $service->approve($review);

        $this->dispatch('review-approved', review: $review->id);
    }

    public function reject(Review $review): void
    {
        $service = app(\App\Domains\Catalog\Services\ReviewService::class);
        $service->reject($review);

        $this->dispatch('review-rejected', review: $review->id);
    }

    public function restore(Review $review): void
    {
        $review->restore();
        $this->dispatch('review-restored', review: $review->id);
    }

    public function getPendingReviewsProperty()
    {
        return Review::pending()
            ->with(['product', 'author', 'order'])
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    public function getTotalPendingProperty(): int
    {
        return Review::pending()->count();
    }

    public function render()
    {
        return view('livewire.admin.review-moderation', [
            'reviews' => $this->pendingReviews,
            'totalPending' => $this->totalPending,
        ]);
    }
}
