<?php

declare(strict_types=1);

namespace App\Domains\Order\Enums;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'En attente de paiement',
            self::PROCESSING => 'En traitement',
            self::SHIPPED => 'Expédiée',
            self::DELIVERED => 'Livrée',
            self::COMPLETED => 'Complétée',
            self::CANCELLED => 'Annulée',
            self::REFUNDED => 'Remboursée',
        };
    }

    /**
     * Get all allowed transitions from this status.
     *
     * @return array<OrderStatus>
     */
    public function getAllowedTransitions(): array
    {
        return match ($this) {
            self::PENDING_PAYMENT => [self::PROCESSING, self::CANCELLED],
            self::PROCESSING => [self::SHIPPED, self::CANCELLED],
            self::SHIPPED => [self::DELIVERED],
            self::DELIVERED => [self::COMPLETED],
            self::COMPLETED => [],
            self::CANCELLED => [],
            self::REFUNDED => [],
        };
    }

    /**
     * Check if transition to target status is allowed.
     */
    public function canTransitionTo(OrderStatus $target): bool
    {
        return in_array($target, $this->getAllowedTransitions());
    }

    /**
     * Get color for UI display.
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'yellow',
            self::PROCESSING => 'blue',
            self::SHIPPED => 'purple',
            self::DELIVERED => 'green',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
            self::REFUNDED => 'orange',
        };
    }
}
