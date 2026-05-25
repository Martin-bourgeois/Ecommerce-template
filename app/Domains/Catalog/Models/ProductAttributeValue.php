<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    protected $table = 'product_attribute_values';

    protected $fillable = [
        'product_variant_id',
        'attribute_id',
        'attribute_option_id',
        'text_value',
        'boolean_value',
    ];

    protected $casts = [
        'boolean_value' => 'boolean',
    ];

    /**
     * Get product variant.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Get attribute.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get attribute option.
     */
    public function attributeOption(): BelongsTo
    {
        return $this->belongsTo(AttributeOption::class);
    }

    /**
     * Get the value (works for all types).
     */
    public function getValue()
    {
        if ($this->attribute_option_id) {
            return $this->attributeOption->value;
        }

        if ($this->text_value) {
            return $this->text_value;
        }

        return $this->boolean_value;
    }
}
