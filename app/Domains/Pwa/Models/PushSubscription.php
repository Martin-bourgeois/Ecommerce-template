<?php

declare(strict_types=1);

namespace App\Domains\Pwa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class PushSubscription extends Model
{
    protected $table = 'push_subscriptions';

    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh',
        'auth',
        'is_active',
        'subscribed_at',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscribed_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Relation: utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Marquer comme utilisée
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Vérifier si la souscription est valide
     */
    public function isValid(): bool
    {
        return $this->is_active && !is_null($this->endpoint);
    }
}
