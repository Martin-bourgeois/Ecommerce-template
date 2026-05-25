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
            self::BESTSELLER => $query->orderBy('popularity', 'desc'),
            self::RATING => $query->leftJoin('product_ratings', 'products.id', '=', 'product_ratings.product_id')
                ->selectRaw('products.*, AVG(product_ratings.rating) as avg_rating')
                ->groupBy('products.id')
                ->orderBy('avg_rating', 'desc'),
        };
    }

    public static function tryFrom(?string $value): ?self
    {
        if (!$value) {
            return null;
        }

        return match ($value) {
            'relevance' => self::RELEVANCE,
            'price_asc' => self::PRICE_ASC,
            'price_desc' => self::PRICE_DESC,
            'newest' => self::NEWEST,
            'bestseller' => self::BESTSELLER,
            'rating' => self::RATING,
            default => null,
        };
    }
}
