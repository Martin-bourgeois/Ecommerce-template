<?php

declare(strict_types=1);

namespace App\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\InteractsWithMedia;

trait HasCategoryMedia
{
    use InteractsWithMedia;

    /**
     * Register media collections for categories.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile()
            ->useDisk('public');

        $this->addMediaCollection('banner')
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
    }

    /**
     * Get category image URL.
     */
    public function getImageUrl(string $conversion = 'medium'): ?string
    {
        $media = $this->getFirstMedia('image');

        if (!$media) {
            return null;
        }

        return $conversion === 'original'
            ? $media->getUrl()
            : $media->getUrl($conversion);
    }

    /**
     * Get category banner URL.
     */
    public function getBannerUrl(string $conversion = 'large'): ?string
    {
        $media = $this->getFirstMedia('banner');

        if (!$media) {
            return null;
        }

        return $conversion === 'original'
            ? $media->getUrl()
            : $media->getUrl($conversion);
    }

    /**
     * Get thumbnail (300x300).
     */
    public function getThumbnailUrl(): ?string
    {
        return $this->getImageUrl('thumb');
    }

    /**
     * Check if category has image.
     */
    public function hasImage(): bool
    {
        return $this->hasMedia('image');
    }

    /**
     * Check if category has banner.
     */
    public function hasBanner(): bool
    {
        return $this->hasMedia('banner');
    }
}
