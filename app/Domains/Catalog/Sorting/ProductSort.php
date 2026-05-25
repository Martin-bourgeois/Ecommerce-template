<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Sorting;

use Illuminate\Database\Eloquent\Builder;

enum ProductSort: string
{
    case RELEVANCE = 'relevance';
    case PRICE_ASC = 'price_asc';
    case PRICE_DESC = 'price_desc';
    case NEWEST = 'newest';
    case BESTSELLER = 'bestseller';
    case RATING = 'rating';

    public function apply(Builder $query): Builder
    {
        return match ($this) {
            self::RELEVANCE => $query,
            self::PRICE_ASC => $query->orderBy('price', 'asc'),
            self::PRICE_DESC => $query->orderBy('price', 'desc'),
            self::NEWEST => $query->orderBy('created_at', 'desc'),
            self::BESTSELLER => $query->orderBy('created_at', 'desc'), // Fallback to newest
            self::RATING => $query->orderBy('created_at', 'desc'), // Fallback to newest
        };
    }
}
