<?php

declare(strict_types=1);

namespace App\Livewire\Product;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Review;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ReviewsList extends Component
{
    use WithPagination;

    public Product $product;

    #[Url]
    public int $rating = 0;

    #[Url]
    public string $sortBy = 'newest'; // newest, helpful

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function filterByRating(int $rating): void
    {
        $this->rating = $rating;
        $this->resetPage();
    }

    public function sortBy(string $sort): void
    {
        $this->sortBy = $sort;
        $this->resetPage();
    }

    public function markHelpful(Review $review): void
    {
        if (!Auth::check()) {
            $this->dispatch('need-login');
            return;
        }

        $service = app(\App\Domains\Catalog\Services\ReviewService::class);
        $marked = $service->markHelpful($review, Auth::user());

        if ($marked) {
            $this->dispatch('review-marked-helpful');
            $this->dispatch('$refresh');
        }
    }

    public function getReviewsProperty()
    {
        $query = Review::where('product_id', $this->product->id)
            ->approved();

        // Filter by rating
        if ($this->rating > 0) {
            $query->where('rating', $this->rating);
        }

        // Sort
        if ($this->sortBy === 'helpful') {
            $query->orderByDesc('helpful_count');
        } else {
            $query->orderByDesc('created_at');
        }

        return $query->paginate(5);
    }

    public function getRatingDistributionProperty()
    {
        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = Review::where('product_id', $this->product->id)
                ->approved()
                ->where('rating', $i)
                ->count();
            $distribution[$i] = $count;
        }
        return $distribution;
    }

    public function getAverageRatingProperty(): float
    {
        return Review::where('product_id', $this->product->id)
            ->approved()
            ->avg('rating') ?? 0;
    }

    public function getTotalReviewsProperty(): int
    {
        return Review::where('product_id', $this->product->id)
            ->approved()
            ->count();
    }

    public function render()
    {
        return view('livewire.product.reviews-list', [
            'reviews' => $this->reviews,
            'ratingDistribution' => $this->ratingDistribution,
            'averageRating' => $this->averageRating,
            'totalReviews' => $this->totalReviews,
        ]);
    }
}
