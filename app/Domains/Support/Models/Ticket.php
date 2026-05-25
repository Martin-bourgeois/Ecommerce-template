<?php

declare(strict_types=1);

namespace App\Domains\Support\Models;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\User;
use App\Domains\Order\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'assigned_to',
        'order_id',
        'category',
        'priority',
        'status',
        'subject',
        'description',
    ];

    protected $casts = [
        'category' => TicketCategory::class,
        'priority' => TicketPriority::class,
        'status' => TicketStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', '!=', TicketStatus::CLOSED);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', TicketStatus::CLOSED);
    }

    public function scopeByStatus($query, TicketStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, TicketCategory $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, TicketPriority $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, User $user)
    {
        return $query->where('assigned_to', $user->id);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')");
    }

    public function isOverdue(): bool
    {
        if ($this->status === TicketStatus::RESOLVED || $this->status === TicketStatus::CLOSED) {
            return false;
        }

        $slaHours = $this->priority->slaHours();
        $dueTime = $this->created_at->addHours($slaHours);

        return now()->isAfter($dueTime);
    }

    public function slaRemainingHours(): ?float
    {
        if ($this->status === TicketStatus::RESOLVED || $this->status === TicketStatus::CLOSED) {
            return null;
        }

        $slaHours = $this->priority->slaHours();
        $dueTime = $this->created_at->addHours($slaHours);
        $remaining = $dueTime->diffInMinutes(now(), false) / 60;

        return max(0, $remaining);
    }

    public function lastMessage(): ?TicketMessage
    {
        return $this->messages()->latest()->first();
    }

    public function lastPublicMessage(): ?TicketMessage
    {
        return $this->messages()
            ->where('is_internal', false)
            ->latest()
            ->first();
    }

    public function getLastReplyFromClient(): ?TicketMessage
    {
        return $this->messages()
            ->where('user_id', $this->user_id)
            ->latest()
            ->first();
    }

    public function getLastReplyFromStaff(): ?TicketMessage
    {
        return $this->messages()
            ->where('user_id', '!=', $this->user_id)
            ->latest()
            ->first();
    }
}
