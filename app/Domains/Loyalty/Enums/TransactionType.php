<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\Enums;

enum TransactionType: string
{
    case EARNED = 'earned';
    case SPENT = 'spent';
    case REFUNDED = 'refunded';
    case EXPIRED = 'expired';
    case BONUS = 'bonus';

    public function label(): string
    {
        return match ($this) {
            self::EARNED => 'Points gagnés',
            self::SPENT => 'Points dépensés',
            self::REFUNDED => 'Points remboursés',
            self::EXPIRED => 'Points expirés',
            self::BONUS => 'Bonus points',
        };
    }

    public function isCredit(): bool
    {
        return in_array($this, [self::EARNED, self::REFUNDED, self::BONUS]);
    }

    public function isDebit(): bool
    {
        return in_array($this, [self::SPENT, self::EXPIRED]);
    }
}
