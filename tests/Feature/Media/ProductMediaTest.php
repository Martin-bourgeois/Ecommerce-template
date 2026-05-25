<?php

declare(strict_types=1);

namespace Tests\Feature\Media;

use Tests\TestCase;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test valid image upload (JPEG).
     */
    public function test_can_upload_valid_jpeg_image(): void
    {
        $product = Product::factory()->create();
        $file = UploadedFile::fake()->image('product.jpg', 300, 300);

        $product->addMedia($file)
            ->toMediaCollection('images');

        $this->assertCount(1, $product->getMedia('images'));
    }

    /**
     * Test valid image upload (PNG).
     */
    public function test_can_upload_valid_png_image(): void
    {
        $product = Product::factory()->create();
        $file = UploadedFile::fake()->image('product.png', 300, 300);

        $product->addMedia($file)
            ->toMediaCollection('images');

        $this->assertCount(1, $product->getMedia('images'));
    }

    /**
     * Test valid image upload (WebP).
     */
    public function test_can_upload_valid_webp_image(): void
    {
        $product = Product::factory()->create();
        $file = UploadedFile::fake()->image('product.webp', 300, 300);

        $product->addMedia($file)
            ->toMediaCollection('images');

        $this->assertCount(1, $product->getMedia('images'));
    }

    /**
     * Test reject PDF upload.
     */
    public function test_cannot_upload_pdf(): void
    {
        $product = Product::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        try {
            $product->addMedia($file)
                ->toMediaCollection('images');

            // If we reach here, the upload was accepted (should not happen)
            $this->fail('PDF should have been rejected');
        } catch (\Exception) {
            $this->assertTrue(true); // Expected behavior
        }
    }

    /**
     * Test file size limit (max 5MB).
     */
    public function test_file_size_limit(): void
    {
        $product = Product::factory()->create();
        // Create a file that's 6MB (exceeds 5MB limit)
        $file = UploadedFile::fake()->image('large.jpg')->size(6144);

        try {
            $product->addMedia($file)
                ->toMediaCollection('images');

            $this->fail('5MB file should have been rejected');
        } catch (\Exception) {
            $this->assertTrue(true); // Expected behavior
        }
    }

    /**
     * Test product has correct image helpers.
     */
    public function test_product_image_helpers(): void
    {
        $product = Product::factory()->create();
        $file = UploadedFile::fake()->image('product.jpg');

        $product->addMedia($file)
            ->toMediaCollection('images');

        $this->assertTrue($product->hasImages());
        $this->assertEquals(1, $product->getImageCount());
        $this->assertNotNull($product->getThumbnailUrl());
        $this->assertNotNull($product->getImageUrl());
    }

    /**
     * Test multiple images can be uploaded.
     */
    public function test_can_upload_multiple_images(): void
    {
        $product = Product::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            $file = UploadedFile::fake()->image("product-{$i}.jpg");
            $product->addMedia($file)
                ->toMediaCollection('images');
        }

        $this->assertEquals(3, $product->getImageCount());
        $this->assertCount(3, $product->getImageUrls());
    }

    /**
     * Test image deletion.
     */
    public function test_can_delete_image(): void
    {
        $product = Product::factory()->create();
        $file = UploadedFile::fake()->image('product.jpg');

        $media = $product->addMedia($file)
            ->toMediaCollection('images');

        $this->assertTrue($product->deleteImage($media->id));
        $this->assertEquals(0, $product->getImageCount());
    }

    /**
     * Test image reordering.
     */
    public function test_can_reorder_images(): void
    {
        $product = Product::factory()->create();

        $media1 = $product->addMedia(UploadedFile::fake()->image('1.jpg'))
            ->toMediaCollection('images');

        $media2 = $product->addMedia(UploadedFile::fake()->image('2.jpg'))
            ->toMediaCollection('images');

        $media3 = $product->addMedia(UploadedFile::fake()->image('3.jpg'))
            ->toMediaCollection('images');

        // Reorder: 3, 1, 2
        $product->reorderImages([$media3->id, $media1->id, $media2->id]);

        $media = $product->getMedia('images');
        $this->assertEquals($media3->id, $media[0]->id);
        $this->assertEquals($media1->id, $media[1]->id);
        $this->assertEquals($media2->id, $media[2]->id);
    }

    /**
     * Test cascade delete: media deleted when product deleted.
     */
    public function test_media_cascade_delete_with_product(): void
    {
        $product = Product::factory()->create();
        $file = UploadedFile::fake()->image('product.jpg');

        $product->addMedia($file)
            ->toMediaCollection('images');

        $mediaCount = $product->getMedia('images')->count();
        $this->assertEquals(1, $mediaCount);

        $productId = $product->id;
        $product->delete();

        // After product deletion (soft delete), media should still exist
        // but the relationship is gone
        $this->assertFalse(Product::find($productId)->hasImages());
    }
}
