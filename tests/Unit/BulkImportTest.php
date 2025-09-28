<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessProductCsv;
use League\Csv\Writer;

class BulkImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations for in-memory DB
        Artisan::call('migrate:fresh');

        // Clear cache
        Cache::flush();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_imports_and_updates_products_from_csv()
    {
        // Arrange: create a CSV file with 2 products
        $csvPath = storage_path('app/test_products.csv');
        $csv = Writer::createFromPath($csvPath, 'w+');
        $csv->insertOne(['sku', 'name', 'description', 'price']);
        $csv->insertAll([
            ['SKU001', 'Product A', 'Desc A', '10.5'],
            ['SKU002', 'Product B', 'Desc B', '20.0'],
        ]);

        // Insert an existing product with SKU002 (to check update)
        DB::table('products')->insert([
            'sku' => 'SKU002',
            'title' => 'Old Product B',
            'description' => 'Old Desc',
            'price' => 15.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cacheKey = 'test_csv_import';

        // Act
        $job = new ProcessProductCsv($csvPath, $cacheKey);
        $job->handle();

        // Assert: SKU001 inserted
        $this->assertDatabaseHas('products', [
            'sku' => 'SKU001',
            'title' => 'Product A',
            'price' => 10.5,
        ]);

        // Assert: SKU002 updated
        $this->assertDatabaseHas('products', [
            'sku' => 'SKU002',
            'title' => 'Product B',
            'price' => 20.0,
        ]);

        // Assert summary in cache
        $summary = Cache::get($cacheKey . '_summary');
        $this->assertEquals(2, $summary['processed']);
        $this->assertEquals(1, $summary['imported']);
        $this->assertEquals(1, $summary['updated']);
    }
}
