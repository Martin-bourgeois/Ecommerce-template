<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Filters;

use Illuminate\Database\Eloquent\Builder;

class AttributeFilter extends ProductFilter
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if (!is_array($value) || empty($value)) {
            return $query;
        }

        // $value format: ['size' => ['M', 'L'], 'color' => ['red', 'blue']]
        foreach ($value as $attributeSlug => $optionValues) {
            if (empty($optionValues)) {
                continue;
            }

            $values = is_array($optionValues) ? $optionValues : [$optionValues];

            $query->whereHas('variants.attributeValues', function (Builder $q) use ($attributeSlug, $values) {
                $q->whereHas('attribute', function (Builder $subQ) use ($attributeSlug) {
                    $subQ->where('slug', $attributeSlug);
                })
                ->whereHas('attributeOption', function (Builder $subQ) use ($values) {
                    $subQ->whereIn('value', $values);
                });
            });
        }

        return $query;
    }
}
