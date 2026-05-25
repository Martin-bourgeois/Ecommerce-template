<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Filters;

use Illuminate\Database\Eloquent\Builder;

class CategoryFilter extends ProductFilter
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if (!$value) {
            return $query;
        }

        $categories = is_array($value) ? $value : [$value];
        return $query->whereIn('category_id', $categories);
    }
}
