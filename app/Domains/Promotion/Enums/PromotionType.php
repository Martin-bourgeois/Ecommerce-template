<?php

declare(strict_types=1);

namespace App\Domains\Promotion\Enums;

enum PromotionType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED_AMOUNT = 'fixed_amount';
    case FREE_SHIPPING = 'free_shipping';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Pourcentage',
            self::FIXED_AMOUNT => 'Montant fixe',
            self::FREE_SHIPPING => 'Livraison gratuite',
        };
    }

    public function format(int $value): string
    {
        return match ($this) {
            self::PERCENTAGE => $value . '%',
            self::FIXED_AMOUNT => number_format($value / 100, 2, ',', ' ') . '€',
            self::FREE_SHIPPING => 'Gratuit',
        };
    }
}

enum PromotionStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case DISABLED = 'disabled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::ACTIVE => 'Actif',
            self::EXPIRED => 'Expiré',
            self::DISABLED => 'Désactivé',
        };
    }
}
