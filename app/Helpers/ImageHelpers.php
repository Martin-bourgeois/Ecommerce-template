<?php

declare(strict_types=1);

use App\Domains\Catalog\Models\Product;

if (!function_exists('product_image_url')) {
    /**
     * Get product image URL by conversion.
     *
     * @param Product $product
     * @param string $conversion The conversion name (thumb, medium, large, responsive, original)
     * @return string|null The image URL or null if no image exists
     */
    function product_image_url(Product $product, string $conversion = 'medium'): ?string
    {
        return $product->getImageUrl($conversion);
    }
}

if (!function_exists('product_image_srcset')) {
    /**
     * Get responsive image srcset for product.
     *
     * @param Product $product
     * @return string|null The srcset HTML attribute or null if no image exists
     */
    function product_image_srcset(Product $product): ?string
    {
        return $product->getResponsiveImageSrcset();
    }
}

if (!function_exists('product_images_urls')) {
    /**
     * Get all product image URLs.
     *
     * @param Product $product
     * @param string $conversion The conversion name
     * @return array Array of image URLs
     */
    function product_images_urls(Product $product, string $conversion = 'medium'): array
    {
        return $product->getImageUrls($conversion);
    }
}

if (!function_exists('product_thumbnail_url')) {
    /**
     * Get product thumbnail URL (300x300).
     *
     * @param Product $product
     * @return string|null The thumbnail URL or null if no image exists
     */
    function product_thumbnail_url(Product $product): ?string
    {
        return $product->getThumbnailUrl();
    }
}
