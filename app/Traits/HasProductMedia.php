<?php

declare(strict_types=1);

namespace App\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\InteractsWithMedia;

trait HasProductMedia
{
    use InteractsWithMedia;

    /**
     * Register media collections for products.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');

        $this->addMediaCollection('thumbnail')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile()
            ->useDisk('public');
    }

    /**
     * Register conversions.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        // Thumbnail: 300x300 crop
        $this->addMediaConversion('thumb')
            ->fit('crop', 300, 300)
            ->format('webp')
            ->quality(80)
            ->nonQueued();

        // Medium: 600x600 fit
        $this->addMediaConversion('medium')
            ->fit('contain', 600, 600)
            ->format('webp')
            ->quality(85)
            ->queued();

        // Large: 1200x1200 fit
        $this->addMediaConversion('large')
            ->fit('contain', 1200, 1200)
            ->format('webp')
            ->quality(90)
            ->queued();

        // Responsive images
        $this->addMediaConversion('responsive')
            ->format('webp')
            ->quality(85)
            ->responsive()
            ->queued();
    }

    /**
     * Get primary image URL.
     */
    public function getImageUrl(string $conversion = 'medium'): ?string
    {
        $media = $this->getFirstMedia('images');

        if (!$media) {
            return null;
        }

        return $conversion === 'original'
            ? $media->getUrl()
            : $media->getUrl($conversion);
    }

    /**
     * Get all image URLs.
     */
    public function getImageUrls(string $conversion = 'medium'): array
    {
        return $this->getMedia('images')
            ->map(fn ($media) => $conversion === 'original'
                ? $media->getUrl()
                : $media->getUrl($conversion)
            )
            ->toArray();
    }

    /**
     * Get thumbnail URL (first image).
     */
    public function getThumbnailUrl(): ?string
    {
        return $this->getImageUrl('thumb');
    }

    /**
     * Get responsive image srcset.
     */
    public function getResponsiveImageSrcset(): ?string
    {
        $media = $this->getFirstMedia('images');

        if (!$media) {
            return null;
        }

        return $media->getSrcset('responsive');
    }

    /**
     * Check if product has images.
     */
    public function hasImages(): bool
    {
        return $this->hasMedia('images');
    }

    /**
     * Get image count.
     */
    public function getImageCount(): int
    {
        return $this->getMedia('images')->count();
    }

    /**
     * Add image and set as primary if first.
     */
    public function addImage($file, string $collectionName = 'images'): Media
    {
        return $this->addMedia($file)
            ->toMediaCollection($collectionName);
    }

    /**
     * Delete specific image.
     */
    public function deleteImage(int $mediaId): bool
    {
        $media = $this->getMedia('images')->where('id', $mediaId)->first();

        if (!$media) {
            return false;
        }

        $media->delete();

        return true;
    }

    /**
     * Reorder images.
     */
    public function reorderImages(array $mediaIds): void
    {
        foreach ($mediaIds as $order => $mediaId) {
            $this->getMedia('images')
                ->where('id', $mediaId)
                ->first()
                ?->update(['order_column' => $order]);
        }
    }
}
