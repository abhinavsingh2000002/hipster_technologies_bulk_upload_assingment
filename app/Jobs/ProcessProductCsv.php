<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

class ProcessProductCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $cacheKey;
    protected $batchSize = 1000;
    protected $cacheUpdateInterval = 200; // update cache every 200 rows

    public function __construct($filePath, $cacheKey)
    {
        $this->filePath = $filePath;
        $this->cacheKey = $cacheKey;

        // Clear old cache
        Cache::forget($this->cacheKey . '_total');
        Cache::forget($this->cacheKey . '_processed');
        Cache::forget($this->cacheKey . '_summary');
    }

    public function handle()
    {
        $csv = Reader::createFromPath($this->filePath, 'r');
        $csv->setHeaderOffset(0);

        $rows = iterator_to_array($csv->getRecords());
        $totalRows = count($rows);
        Cache::put($this->cacheKey . '_total', $totalRows, 3600);

        $summary = [
            'total' => $totalRows,
            'processed' => 0,
            'imported' => 0,
            'updated' => 0,
            'invalid' => 0,
            'duplicates' => 0,
        ];

        $batch = [];
        $csvSkus = []; // track SKUs within this CSV
        $counter = 0;

        foreach ($rows as $row) {
            $counter++;
            $summary['processed']++;

            $sku = trim($row['sku'] ?? '');
            $name = trim($row['name'] ?? '');
            $price = $row['price'] ?? null;

            // Invalid rows
            if ($sku === '' || $name === '' || ($price !== null && !is_numeric($price))) {
                $summary['invalid']++;
                $this->maybeUpdateCache($summary, $counter);
                continue;
            }

            // Duplicate in CSV
            if (isset($csvSkus[$sku])) {
                $summary['duplicates']++;
                $this->maybeUpdateCache($summary, $counter);
                continue;
            }

            $csvSkus[$sku] = true;

            $batch[] = [
                'sku' => $sku,
                'title' => $name,
                'description' => $row['description'] ?? null,
                'price' => $price,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Upsert batch
            if (count($batch) >= $this->batchSize) {
                $this->upsertBatch($batch, $summary);
                $batch = [];
            }

            $this->maybeUpdateCache($summary, $counter);
        }

        // Remaining batch
        if (!empty($batch)) {
            $this->upsertBatch($batch, $summary);
        }

        // Final summary cache update
        $this->updateCache($summary);
    }

    protected function upsertBatch(array $batch, array &$summary)
    {
        if (empty($batch)) return;

        $batchSkus = collect($batch)->pluck('sku')->toArray();

        // Check existing SKUs before upsert
        $existingSkus = DB::table('products')
            ->whereIn('sku', $batchSkus)
            ->pluck('sku')
            ->toArray();

        $newSkus = array_diff($batchSkus, $existingSkus);
        $updateSkus = array_intersect($batchSkus, $existingSkus);

        try {
            DB::beginTransaction();

            // Perform upsert
            DB::table('products')->upsert(
                $batch,
                ['sku'],
                ['title', 'description', 'price', 'updated_at']
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // optionally log the error
            Log::error("Product CSV upsert failed: " . $e->getMessage());
            return;
        }

        // Update counts
        $summary['imported'] += count($newSkus);
        $summary['updated']  += count($updateSkus);
    }

    protected function maybeUpdateCache(array &$summary, int $counter)
    {
        $totalRows = Cache::get($this->cacheKey . '_total', 1);

        if ($counter % $this->cacheUpdateInterval === 0 || $summary['processed'] >= $totalRows) {
            $this->updateCache($summary);
        }
    }

    protected function updateCache(array $summary)
    {
        Cache::put($this->cacheKey . '_processed', $summary['processed'], 3600);
        Cache::put($this->cacheKey . '_summary', $summary, 3600);
    }
}
