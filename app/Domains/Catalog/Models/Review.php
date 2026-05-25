<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Models;

use App\Models\User;
use App\Domains\Order\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Review extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'is_approved',
        'verified_purchase',
        'helpful_count',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'verified_purchase' => 'boolean',
        'helpful_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the product that was reviewed.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created the review.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the order associated with this review.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get helpful votes for this review.
     */
    public function helpfulVotes(): HasMany
    {
        return $this->hasMany(ReviewHelpful::class);
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->useDisk('public')
            ->singleFile();
    }

    /**
     * Scope: Only approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope: Only pending reviews.
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false)->whereNull('deleted_at');
    }

    /**
     * Scope: Only verified purchases.
     */
    public function scopeVerifiedPurchases($query)
    {
        return $query->where('verified_purchase', true);
    }

    /**
     * Scope: Filter by rating.
     */
    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope: Filter by minimum rating.
     */
    public function scopeMinRating($query, int $minRating)
    {
        return $query->where('rating', '>=', $minRating);
    }

    /**
     * Scope: Order by helpful count.
     */
    public function scopeOrderByHelpful($query)
    {
        return $query->orderByDesc('helpful_count');
    }

    /**
     * Scope: Order by date (newest first).
     */
    public function scopeOrderByNewest($query)
    {
        return $query->orderByDesc('created_at');
    }

    /**
     * Check if review has photos.
     */
    public function hasPhotos(): bool
    {
        return $this->getMedia('photos')->count() > 0;
    }

    /**
     * Get photos count.
     */
    public function getPhotosCount(): int
    {
        return $this->getMedia('photos')->count();
    }

    /**
     * Check if user has voted this review as helpful.
     */
    public function isMarkedHelpfulBy(User $user): bool
    {
        return $this->helpfulVotes()
            ->where('user_id', $user->id)
            ->exists();
    }
}
