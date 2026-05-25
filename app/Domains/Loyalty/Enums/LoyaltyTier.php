<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\Enums;

enum LoyaltyTier: string
{
    case BRONZE = 'bronze';
    case SILVER = 'silver';
    case GOLD = 'gold';
    case PLATINUM = 'platinum';

    public function label(): string
    {
        return match ($this) {
            self::BRONZE => 'Bronze',
            self::SILVER => 'Argent',
            self::GOLD => 'Or',
            self::PLATINUM => 'Platine',
        };
    }

    public function getThreshold(): int
    {
        return match ($this) {
            self::BRONZE => 0,
            self::SILVER => 500,
            self::GOLD => 1500,
            self::PLATINUM => 5000,
        };
    }

    public function getDiscountPercent(): int
    {
        return match ($this) {
            self::BRONZE => 0,
            self::SILVER => 3,
            self::GOLD => 5,
            self::PLATINUM => 10,
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::BRONZE => 'amber',
            self::SILVER => 'gray',
            self::GOLD => 'yellow',
            self::PLATINUM => 'blue',
        };
    }

    /**
     * Get tier for given total earned points
     */
    public static function getTierForPoints(int $totalEarned): self
    {
        return match (true) {
            $totalEarned >= self::PLATINUM->getThreshold() => self::PLATINUM,
            $totalEarned >= self::GOLD->getThreshold() => self::GOLD,
            $totalEarned >= self::SILVER->getThreshold() => self::SILVER,
            default => self::BRONZE,
        };
    }
}
