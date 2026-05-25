<?php

declare(strict_types=1);

namespace App\Livewire\Product;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Review;
use App\Domains\Catalog\Events\ReviewSubmitted;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class ReviewForm extends Component
{
    use WithFileUploads;

    public Product $product;

    public int $rating = 5;
    public string $title = '';
    public string $comment = '';
    public array $photos = [];

    public bool $loading = false;
    public string $successMessage = '';
    public string $errorMessage = '';
    public bool $hasOrder = false;

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->checkIfUserHasOrder();
    }

    public function checkIfUserHasOrder(): void
    {
        if (!Auth::check()) {
            return;
        }

        $this->hasOrder = \App\Domains\Order\Models\Order::whereHas('items', function ($query) {
            $query->where('product_id', $this->product->id);
        })
            ->where('user_id', Auth::id())
            ->whereIn('status', ['delivered', 'completed'])
            ->exists();
    }

    public function submit(): void
    {
        $this->loading = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $this->validate([
                'rating' => 'required|integer|min:1|max:5',
                'title' => 'required|string|max:255',
                'comment' => 'required|string|min:20|max:5000',
                'photos.*' => 'image|max:2048',
            ]);

            if (!Auth::check()) {
                $this->errorMessage = 'Vous devez être connecté pour laisser un avis.';
                return;
            }

            // Get user's delivered order with this product
            $order = \App\Domains\Order\Models\Order::whereHas('items', function ($query) {
                $query->where('product_id', $this->product->id);
            })
                ->where('user_id', Auth::id())
                ->whereIn('status', ['delivered', 'completed'])
                ->latest()
                ->first();

            if (!$order) {
                $this->errorMessage = 'Vous devez avoir une commande livrée pour laisser un avis.';
                return;
            }

            // Check if review already exists
            $existingReview = Review::where('product_id', $this->product->id)
                ->where('order_id', $order->id)
                ->first();

            if ($existingReview) {
                $this->errorMessage = 'Vous avez déjà laissé un avis pour ce produit de cette commande.';
                return;
            }

            // Create review
            $service = app(\App\Domains\Catalog\Services\ReviewService::class);
            $review = $service->createReview(
                order: $order,
                product: $this->product,
                user: Auth::user(),
                data: [
                    'rating' => $this->rating,
                    'title' => $this->title,
                    'comment' => $this->comment,
                    'photos' => $this->photos,
                ]
            );

            // Dispatch event for loyalty points
            ReviewSubmitted::dispatch($review);

            $this->successMessage = 'Avis soumis avec succès! ' . ($review->is_approved ? 'Il est maintenant visible.' : 'Il sera modéré avant publication.');

            // Reset form
            $this->reset(['rating', 'title', 'comment', 'photos']);

            $this->dispatch('review-submitted');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->errorMessage = 'Erreur de validation: ' . implode(', ', $e->errors()['comment'] ?? []);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function removePhoto(int $index): void
    {
        array_splice($this->photos, $index, 1);
    }

    public function render()
    {
        return view('livewire.product.review-form', [
            'canReview' => Auth::check() && $this->hasOrder,
        ]);
    }
}
