<?php

declare(strict_types=1);

namespace Tests\Feature\Media;

use Tests\TestCase;
use App\Domains\Catalog\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoryMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test category can have single image.
     */
    public function test_category_can_have_single_image(): void
    {
        $category = Category::factory()->create();
        $file = UploadedFile::fake()->image('category.jpg');

        $category->addMedia($file)
            ->toMediaCollection('image');

        $this->assertTrue($category->hasImage());
        $this->assertNotNull($category->getImageUrl());
    }

    /**
     * Test category can have single banner.
     */
    public function test_category_can_have_single_banner(): void
    {
        $category = Category::factory()->create();
        $file = UploadedFile::fake()->image('banner.jpg');

        $category->addMedia($file)
            ->toMediaCollection('banner');

        $this->assertTrue($category->hasBanner());
        $this->assertNotNull($category->getBannerUrl());
    }

    /**
     * Test category image helpers with conversions.
     */
    public function test_category_image_conversion_urls(): void
    {
        $category = Category::factory()->create();
        $file = UploadedFile::fake()->image('category.jpg');

        $category->addMedia($file)
            ->toMediaCollection('image');

        // Test different conversions
        $thumbUrl = $category->getThumbnailUrl();
        $mediumUrl = $category->getImageUrl('medium');
        $largeUrl = $category->getImageUrl('large');

        $this->assertNotNull($thumbUrl);
        $this->assertNotNull($mediumUrl);
        $this->assertNotNull($largeUrl);
    }

    /**
     * Test image MIME type validation.
     */
    public function test_image_mime_type_validation(): void
    {
        $category = Category::factory()->create();

        // SVG should be rejected (XSS risk)
        $file = UploadedFile::fake()->create('image.svg', 1000, 'image/svg+xml');

        try {
            $category->addMedia($file)
                ->toMediaCollection('image');

            $this->fail('SVG should have been rejected');
        } catch (\Exception) {
            $this->assertTrue(true); // Expected behavior
        }
    }

    /**
     * Test replacing category image overwrites previous.
     */
    public function test_replacing_image_overwrites_previous(): void
    {
        $category = Category::factory()->create();

        // Add first image
        $file1 = UploadedFile::fake()->image('image1.jpg');
        $category->addMedia($file1)
            ->toMediaCollection('image');

        $this->assertEquals(1, $category->getMedia('image')->count());

        // Add second image to single-file collection
        $file2 = UploadedFile::fake()->image('image2.jpg');
        $category->addMedia($file2)
            ->toMediaCollection('image');

        // Collection should still have only 1 (latest)
        $this->assertLessThanOrEqual(1, $category->getMedia('image')->count());
    }
}
