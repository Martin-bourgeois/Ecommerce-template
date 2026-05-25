<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Filters;

use Illuminate\Database\Eloquent\Builder;

class PriceRangeFilter extends ProductFilter
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if (!is_array($value)) {
            return $query;
        }

        $min = $value['min'] ?? null;
        $max = $value['max'] ?? null;

        if ($min !== null) {
            $query->where('price', '>=', (int)$min);
        }

        if ($max !== null) {
            $query->where('price', '<=', (int)$max);
        }

        return $query;
    }
}
