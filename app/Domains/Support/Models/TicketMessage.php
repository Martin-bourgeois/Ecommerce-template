<?php

declare(strict_types=1);

namespace App\Domains\Support\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    use HasFactory;
    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isFromClient(): bool
    {
        return $this->user_id === $this->ticket->user_id;
    }

    public function isFromStaff(): bool
    {
        return !$this->isFromClient();
    }

    public function isVisible(User $user): bool
    {
        // Messages internes visibles seulement aux staff
        if ($this->is_internal) {
            return $user->can('support.manage');
        }

        // Messages publics visibles à tous les participants
        return $user->id === $this->user_id || $user->id === $this->ticket->assigned_to || $user->can('support.manage');
    }
}
