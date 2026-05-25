<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class ProductFilter
{
    abstract public function apply(Builder $query, mixed $value): Builder;
}
