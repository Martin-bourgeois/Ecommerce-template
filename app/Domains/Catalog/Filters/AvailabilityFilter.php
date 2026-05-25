<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Filters;

use Illuminate\Database\Eloquent\Builder;

class AvailabilityFilter extends ProductFilter
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if (!$value || $value === 'all') {
            return $query;
        }

        if ($value === 'in_stock') {
            return $query->whereHas('activeVariants', function (Builder $q) {
                $q->whereRaw('stock > reserved_stock');
            });
        }

        if ($value === 'out_of_stock') {
            return $query->whereDoesntHave('activeVariants', function (Builder $q) {
                $q->whereRaw('stock > reserved_stock');
            });
        }

        return $query;
    }
}
