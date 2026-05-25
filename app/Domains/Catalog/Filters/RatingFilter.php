<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Filters;

use Illuminate\Database\Eloquent\Builder;

class RatingFilter extends ProductFilter
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if (!$value || $value < 1 || $value > 5) {
            return $query;
        }

        return $query->whereHas('ratings', function (Builder $q) use ($value) {
            $q->where('rating', '>=', (int)$value)
                ->where('is_approved', true);
        });
    }
}
