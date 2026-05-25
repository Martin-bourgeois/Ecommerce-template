<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewHelpful extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'review_id',
        'user_id',
    ];

    /**
     * Get the review.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Get the user who marked as helpful.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
