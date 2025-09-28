<?php

namespace Tests\Unit\Jobs;

use App\Jobs\GenerateImageVariants;
use App\Models\Image;
use App\Models\Product;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateImageVariantsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Optional: Disable foreign key checks for SQLite if needed
        // \DB::statement('PRAGMA foreign_keys=OFF;');
    }

    public function test_generate_image_variants_creates_files_and_updates_db()
    {
        // Step 1: Fake storage
        Storage::fake('public');

        // Step 2: Create a dummy product
        $product = Product::create([
            'title' => 'Test Product',
            'sku' => 'TESTSKU',
            'description' => 'test description',
            'price' => 1000,
        ]);

        // Step 3: Create a real image file (100x200)
        $tempImage = imagecreatetruecolor(100, 200);
        $path = 'uploads/originals/test.jpg';
        $tmpFile = tempnam(sys_get_temp_dir(), 'imgunit_');
        imagejpeg($tempImage, $tmpFile, 90);
        imagedestroy($tempImage);
        Storage::disk('public')->put($path, file_get_contents($tmpFile));
        unlink($tmpFile);

        // Step 4: Create Upload record manually
        $upload = Upload::create([
            'filename' => 'test.jpg',
            'size' => 100,
            'disk' => 'public',
            'path' => $path,
            'status' => 'pending',
            'checksum' => null,
            'dzuuid' => 'test-uuid',
        ]);

        // Step 5: Create Image record manually
        $image = Image::create([
            'upload_id' => $upload->id,
            'product_id' => $product->id,
            'original_path' => $path,
            'meta' => ['filename' => 'test.jpg'],
        ]);

        // Step 6: Run the job
        (new GenerateImageVariants($image))->handle();
        $image->refresh();
        $upload->refresh();

        // Step 7: Assert DB paths updated
        $this->assertNotNull($image->variant_256);
        $this->assertNotNull($image->variant_512);
        $this->assertNotNull($image->variant_1024);

        // Step 8: Assert files exist in storage
        foreach ([256, 512, 1024] as $size) {
            $variantPath = $image->{"variant_$size"};
            Storage::disk('public')->assertExists($variantPath);

            $fullPath = Storage::disk('public')->path($variantPath);
            [$width, $height] = getimagesize($fullPath);

            $this->assertTrue(
                $width === $size || $height === $size,
                "Variant {$size}px has wrong dimension {$width}x{$height}"
            );
        }

        // Step 9: Assert Upload status updated
        $this->assertEquals('done', $upload->status);
    }

    public function test_invalid_image_sets_upload_failed()
    {
        Storage::fake('public');

        // Create dummy product
        $product = Product::create([
            'title' => 'Test Product',
            'sku' => 'TESTSKU',
            'description' => 'test description',
            'price' => 1000,
        ]);

        // Create a "corrupt" file
        $path = 'uploads/originals/corrupt.jpg';
        Storage::disk('public')->put($path, 'not-an-image');

        // Create Upload record
        $upload = Upload::create([
            'filename' => 'corrupt.jpg',
            'size' => 100,
            'disk' => 'public',
            'path' => $path,
            'status' => 'pending',
            'checksum' => null,
            'dzuuid' => 'corrupt-uuid',
        ]);

        // Create Image record
        $image = Image::create([
            'upload_id' => $upload->id,
            'product_id' => $product->id,
            'original_path' => $path,
            'meta' => ['filename' => 'corrupt.jpg'],
        ]);

        // Run the job
        (new GenerateImageVariants($image))->handle();
        $image->refresh();
        $upload->refresh();

        // Assert Upload failed and no variants created
        $this->assertEquals('failed', $upload->status);
        $this->assertNull($image->variant_256);
        $this->assertNull($image->variant_512);
        $this->assertNull($image->variant_1024);
    }
}
