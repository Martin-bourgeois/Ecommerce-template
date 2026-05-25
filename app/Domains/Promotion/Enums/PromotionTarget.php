<?php

declare(strict_types=1);

namespace App\Domains\Promotion\Enums;

enum PromotionTarget: string
{
    case ORDER = 'order';
    case PRODUCT = 'product';
    case CATEGORY = 'category';

    public function label(): string
    {
        return match ($this) {
            self::ORDER => 'Panier',
            self::PRODUCT => 'Produit spécifique',
            self::CATEGORY => 'Catégorie',
        };
    }
}
