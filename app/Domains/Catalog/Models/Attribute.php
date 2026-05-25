<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attribute extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_filterable',
        'is_searchable',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'is_searchable' => 'boolean',
        'is_required' => 'boolean',
        'type' => 'string',
    ];

    /**
     * Get attribute options.
     */
    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class)->orderBy('sort_order');
    }

    /**
     * Scope: Filterable attributes.
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope: Searchable attributes.
     */
    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    /**
     * Check if attribute is select type.
     */
    public function isSelect(): bool
    {
        return in_array($this->type, ['select', 'multiselect']);
    }

    /**
     * Check if attribute is multiselect.
     */
    public function isMultiselect(): bool
    {
        return $this->type === 'multiselect';
    }

    /**
     * Check if attribute is text.
     */
    public function isText(): bool
    {
        return $this->type === 'text';
    }

    /**
     * Check if attribute is boolean.
     */
    public function isBoolean(): bool
    {
        return $this->type === 'boolean';
    }
}
