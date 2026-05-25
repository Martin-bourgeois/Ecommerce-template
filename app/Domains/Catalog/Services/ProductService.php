<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\ProductAttributeValue;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Enums\ProductStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ProductService
{
    /**
     * Create a configurable product with variants and attributes.
     *
     * @param array $data Product data (name, category_id, description, etc.)
     * @param array $variants Variants data with attributes
     * @return Product
     * @throws \Exception
     */
    public function createConfigurable(array $data, array $variants): Product
    {
        return DB::transaction(function () use ($data, $variants) {
            // Create the main product
            $product = Product::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'type' => ProductType::CONFIGURABLE,
                'status' => $data['status'] ?? ProductStatus::DRAFT,
                'manufacturer' => $data['manufacturer'] ?? null,
                'brand' => $data['brand'] ?? null,
                'seo_title' => $data['seo_title'] ?? $data['name'],
                'seo_description' => $data['seo_description'] ?? null,
            ]);

            // Create variants
            foreach ($variants as $index => $variantData) {
                $sku = $this->generateSku($product, $index + 1);

                $variant = $product->variants()->create([
                    'sku' => $sku,
                    'name' => $variantData['name'] ?? null,
                    'price' => $variantData['price'],
                    'cost' => $variantData['cost'] ?? null,
                    'stock' => $variantData['stock'] ?? 0,
                    'weight' => $variantData['weight'] ?? null,
                    'barcode' => $variantData['barcode'] ?? null,
                    'is_active' => $variantData['is_active'] ?? true,
                    'sort_order' => $index,
                ]);

                // Attach attribute values
                if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
                    foreach ($variantData['attributes'] as $attributeId => $optionId) {
                        ProductAttributeValue::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $attributeId,
                            'attribute_option_id' => $optionId,
                        ]);
                    }
                }
            }

            return $product;
        });
    }

    /**
     * Create a simple product.
     */
    public function createSimple(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'type' => ProductType::SIMPLE,
                'status' => $data['status'] ?? ProductStatus::DRAFT,
                'price' => $data['price'],
                'cost' => $data['cost'] ?? null,
                'weight' => $data['weight'] ?? null,
                'sku' => $data['sku'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'manufacturer' => $data['manufacturer'] ?? null,
                'brand' => $data['brand'] ?? null,
                'seo_title' => $data['seo_title'] ?? $data['name'],
                'seo_description' => $data['seo_description'] ?? null,
            ]);

            // Create single variant for simple product
            $sku = $data['sku'] ?? $this->generateSku($product, 1);
            $product->variants()->create([
                'sku' => $sku,
                'name' => null,
                'price' => $data['price'],
                'cost' => $data['cost'] ?? null,
                'stock' => $data['stock'] ?? 0,
                'weight' => $data['weight'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'is_active' => true,
            ]);

            return $product;
        });
    }

    /**
     * Update stock for a variant.
     */
    public function updateStock(ProductVariant $variant, int $quantity): bool
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Stock cannot be negative');
        }

        $variant->stock = $quantity;
        return (bool) $variant->save();
    }

    /**
     * Deduct stock from variant.
     */
    public function deductStock(ProductVariant $variant, int $quantity): bool
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative');
        }

        if ($variant->stock < $quantity) {
            throw new \InvalidArgumentException('Insufficient stock');
        }

        return $variant->deductStock($quantity);
    }

    /**
     * Reserve stock for a variant.
     */
    public function reserveStock(ProductVariant $variant, int $quantity): bool
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative');
        }

        return $variant->reserveStock($quantity);
    }

    /**
     * Release reserved stock.
     */
    public function releaseStock(ProductVariant $variant, int $quantity): void
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative');
        }

        $variant->releaseStock($quantity);
    }

    /**
     * Generate SKU automatically.
     */
    private function generateSku(Product $product, int $index): string
    {
        $categoryCode = substr(strtoupper($product->category->name), 0, 3);
        $productId = str_pad((string) $product->id, 4, '0', STR_PAD_LEFT);
        $variantId = str_pad((string) $index, 3, '0', STR_PAD_LEFT);

        return "{$categoryCode}-{$productId}-{$variantId}";
    }

    /**
     * Update product status.
     */
    public function updateStatus(Product $product, ProductStatus $status): bool
    {
        return $product->update(['status' => $status]);
    }

    /**
     * Publish product (set to active).
     */
    public function publish(Product $product): bool
    {
        return $this->updateStatus($product, ProductStatus::ACTIVE);
    }

    /**
     * Discontinue product.
     */
    public function discontinue(Product $product): bool
    {
        return $this->updateStatus($product, ProductStatus::DISCONTINUED);
    }
}
