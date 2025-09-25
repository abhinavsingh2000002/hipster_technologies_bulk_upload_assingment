<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use Exception;

class ImageUploadController extends Controller
{
    // Temporary folder for chunk storage (inside storage/app/)
    protected $tmpFolder = 'tmp/uploads';

    /**
     * POST /products/images/upload-chunk
     * Stores a single chunk. If chunk already exists, returns success (resume-safe).
     */
    public function uploadChunk(Request $request)
    {
        dd($request->all());
        $chunkIndex = $request->input('dzchunkindex');
        $uuid = $request->input('dzuuid');
        $file = $request->file('file');
        dd($chunkIndex,$uuid,$file);
        if (!$uuid || !is_numeric($chunkIndex) && $chunkIndex !== '0') {
            return response()->json(['error' => 'Missing uuid or chunk index'], 400);
        }

        if (!$file) {
            return response()->json(['error' => 'Missing chunk file'], 400);
        }

        $chunkDir = $this->tmpFolder . '/' . $uuid;
        Storage::makeDirectory($chunkDir);

        // store chunk using its index as filename
        $chunkPath = $chunkDir . '/' . $chunkIndex;

        // If chunk already exists, skip writing (resume idempotent)
        if (Storage::exists($chunkPath)) {
            return response()->json(['success' => true, 'message' => 'Chunk already uploaded']);
        }

        // Store chunk (binary)
        $file->storeAs($chunkDir, $chunkIndex);

        return response()->json(['success' => true]);
    }

    public function mergeChunks(Request $request)
    {
        $productId = $request->input('product_id');
        $uuid = $request->input('dzuuid');
        $filename = $request->input('filename');
        $checksum = $request->input('checksum') ?: null;

        if (!$productId || !$uuid || !$filename) {
            return response()->json(['error' => 'Invalid request, missing product_id/dzuuid/filename'], 400);
        }

        $chunkDir = $this->tmpFolder . '/' . $uuid;

        if (!Storage::exists($chunkDir)) {
            return response()->json(['error' => 'Chunks not found'], 404);
        }

        // Get chunk files, sort numerically by basename (chunk index)
        $chunkFiles = collect(Storage::files($chunkDir))
            ->sortBy(fn($path) => (int) basename($path))
            ->map(fn($path) => Storage::path($path))
            ->values();

        if ($chunkFiles->isEmpty()) {
            return response()->json(['error' => 'No chunks found'], 422);
        }

        // Build final path
        $safeFilename = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $filename);
        $finalFilename = Str::uuid() . '_' . $safeFilename;
        $finalDir = "products/{$productId}";
        Storage::makeDirectory($finalDir);
        $finalPathStorage = $finalDir . '/' . $finalFilename;
        $finalPathAbsolute = Storage::path($finalPathStorage);

        // Merge into final file (write atomically to temp then move)
        $tempMerge = tempnam(sys_get_temp_dir(), 'merge_');
        $out = fopen($tempMerge, 'wb');

        try {
            foreach ($chunkFiles as $chunk) {
                $in = fopen($chunk, 'rb');
                while (!feof($in)) {
                    $buffer = fread($in, 4096);
                    if ($buffer === false) break;
                    fwrite($out, $buffer);
                }
                fclose($in);
            }
            fclose($out);

            // Move merged temp into storage (atomic)
            Storage::put($finalPathStorage, fopen($tempMerge, 'rb'));
            @unlink($tempMerge);

            // Check checksum if provided (sha256 expected)
            if ($checksum) {
                $localChecksum = hash_file('sha256', Storage::path($finalPathStorage));
                if ($localChecksum !== $checksum) {
                    // delete incomplete final file
                    Storage::delete($finalPathStorage);
                    return response()->json(['error' => 'Checksum mismatch'], 422);
                }
            }

            // Idempotency check: if this filename already attached to this product => no-op
            $existing = ProductImage::where('product_id', $productId)
                ->where('filename', $filename)
                ->first();

            if ($existing) {
                // Clean temp chunks and return existing id
                Storage::deleteDirectory($chunkDir);
                return response()->json(['message' => 'Already attached', 'id' => $existing->id]);
            }

            // Save DB record inside transaction to be concurrency-safe
            $imageRecord = null;
            DB::transaction(function() use (&$imageRecord, $productId, $filename, $finalPathStorage, $checksum) {
                // Create image row, set as primary only if none exist already
                $isPrimary = ProductImage::where('product_id', $productId)->count() === 0;

                $imageRecord = ProductImage::create([
                    'product_id' => $productId,
                    'filename' => $filename,
                    'filepath' => $finalPathStorage,
                    'checksum' => $checksum,
                    'is_primary' => $isPrimary ? true : false,
                ]);
            });

            // Create variants (maintaining aspect ratio)
            // Use Intervention Image for resizing; preserve extension
            $ext = pathinfo($filename, PATHINFO_EXTENSION) ?: 'jpg';
            $variants = [256, 512, 1024];

            foreach ($variants as $size) {
                $img = Image::make(Storage::path($finalPathStorage))->resize($size, null, function ($constraint) {
                    $constraint->aspectRatio();
                });

                // Use same extension as original or jpg fallback
                $variantName = pathinfo($filename, PATHINFO_FILENAME) . "_{$size}." . $ext;
                $variantPath = $finalDir . '/' . $variantName;

                // Encode to appropriate format based on extension
                $encoded = (string) $img->encode($ext);
                Storage::put($variantPath, $encoded);
            }

            // Cleanup chunk dir
            Storage::deleteDirectory($chunkDir);

            return response()->json(['message' => 'Image uploaded', 'id' => $imageRecord->id]);
        } catch (Exception $e) {
            // Clean temp files and bubble up error
            if (isset($out) && is_resource($out)) fclose($out);
            @unlink($tempMerge);
            return response()->json(['error' => 'Merge failed: ' . $e->getMessage()], 500);
        }
    }
}
