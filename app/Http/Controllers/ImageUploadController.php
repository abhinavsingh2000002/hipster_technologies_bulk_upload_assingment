<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateImageVariants;
use App\Models\Image;
use App\Models\Product;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function uploadChunk(Request $request)
    {

        $dzuuid = $request->input('dzuuid') ?? $request->header('dzuuid') ?? $request->input('dzuuid');
        $chunkIndex = $request->input('dzchunkindex');
        $totalChunks = $request->input('dztotalchunkcount');
        $filename = $request->file('file') ? $request->file('file')->getClientOriginalName() : $request->input('filename');
        $productId = $request->input('product_id');
        $checksum = $request->header('X-Upload-Checksum');

        if (! $dzuuid || is_null($chunkIndex)) {
            return response()->json(['ok' => false, 'message' => 'Missing dzuuid or chunk index'], 400);
        }

        $chunk = $request->file('file');
        if (! $chunk) {
            return response()->json(['ok' => false, 'message' => 'No chunk found'], 400);
        }

        $chunkDir = storage_path("app/uploads/chunks/{$dzuuid}");
        if (! is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }

        $tmpName = $chunkDir.DIRECTORY_SEPARATOR.intval($chunkIndex).'.part';

        $stream = fopen($chunk->getRealPath(), 'rb');
        $out = fopen($tmpName.'.tmp', 'wb');
        stream_copy_to_stream($stream, $out);
        fclose($stream);
        fclose($out);
        rename($tmpName.'.tmp', $tmpName);

        return response()->json(['ok' => true]);
    }

    public function finalize(Request $request)
    {

        $data = $request->only(['dzuuid', 'filename', 'size', 'checksum', 'product_id']);
        $dzuuid = $data['dzuuid'] ?? null;
        if (! $dzuuid) {
            return response()->json(['ok' => false, 'message' => 'Missing dzuuid'], 400);
        }

        $chunkDir = storage_path("app/uploads/chunks/{$dzuuid}");
        if (! is_dir($chunkDir)) {
            return response()->json(['ok' => false, 'message' => 'No chunks found'], 400);
        }

        $lockKey = "upload:finalize:{$dzuuid}";
        $lock = Cache::lock($lockKey, 30);

        try {
            $got = $lock->block(10, function () use ($dzuuid, $chunkDir, $data) {
                $finalDir = storage_path('app/uploads/assembled');
                if (! is_dir($finalDir)) {
                    mkdir($finalDir, 0755, true);
                }

                $assembledPath = $finalDir.DIRECTORY_SEPARATOR.$dzuuid.'_'.Str::slug(pathinfo($data['filename'], PATHINFO_FILENAME)).'.'.pathinfo($data['filename'], PATHINFO_EXTENSION);

                $existingUpload = Upload::where('dzuuid', $dzuuid)->first();
                if ($existingUpload && $existingUpload->status === 'assembled' && Storage::disk('local')->exists(str_replace(storage_path('app/'), '', $existingUpload->path))) {
                    return ['ok' => true, 'upload_id' => $existingUpload->id, 'assembled_path' => $existingUpload->path];
                }

                $parts = glob($chunkDir.DIRECTORY_SEPARATOR.'*.part');
                if (! $parts || count($parts) === 0) {
                    return ['ok' => false, 'message' => 'No part files found'];
                }

                usort($parts, function ($a, $b) {
                    return intval(basename($a)) <=> intval(basename($b));
                });

                $tempAssembled = $assembledPath.'.tmp';
                $out = fopen($tempAssembled, 'wb');
                foreach ($parts as $p) {
                    $in = fopen($p, 'rb');
                    stream_copy_to_stream($in, $out);
                    fclose($in);
                }
                fclose($out);
                rename($tempAssembled, $assembledPath);

                $computed = md5_file($assembledPath);
                $clientChecksum = $data['checksum'] ?? null;

                $upload = Upload::firstOrCreate(
                    ['dzuuid' => $dzuuid],
                    [
                        'filename' => $data['filename'] ?? basename($assembledPath),
                        'size' => $data['size'] ?? filesize($assembledPath),
                        'disk' => 'public',
                        'path' => str_replace(storage_path('app/'), '', $assembledPath), // store relative path
                        'status' => 'assembled',
                        'checksum' => $computed,
                    ]
                );

                if ($clientChecksum && ($clientChecksum !== $computed)) {
                    $upload->update(['status' => 'checksum_failed', 'checksum' => $computed]);

                    return ['ok' => false, 'message' => 'Checksum mismatch', 'computed' => $computed, 'client' => $clientChecksum, 'upload_id' => $upload->id];
                }

                $upload->update(['status' => 'assembled', 'checksum' => $computed, 'path' => str_replace(storage_path('app/'), '', $assembledPath)]);

                return ['ok' => true, 'upload_id' => $upload->id, 'assembled_path' => $upload->path];
            });

            return response()->json($got);

        } catch (\Exception $e) {
            Log::error("Finalize error for {$dzuuid}: ".$e->getMessage());

            return response()->json(['ok' => false, 'message' => 'Server error finalizing upload'], 500);
        } finally {
            optional($lock)->release();
        }
    }

    public function attach(Request $request)
    {
        $data = $request->validate([
            'upload_id' => 'required|integer|exists:uploads,id',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $upload = Upload::findOrFail($data['upload_id']);
        $product = Product::findOrFail($data['product_id']);

        $existing = DB::table('images')->where('product_id', $product->id)->where('upload_id', $upload->id)->first();
        if ($existing) {
            return response()->json(['ok' => true, 'message' => 'Already attached', 'image_id' => $existing->id]);
        }

        $relativePath = $upload->path;
        $sourceFull = storage_path('app/'.$relativePath);
        $destinationRel = 'uploads/originals/'.basename($relativePath);
        Storage::disk('public')->putFileAs('uploads/originals', $sourceFull, basename($relativePath));

        $image = Image::create([
            'product_id' => $product->id,
            'upload_id' => $upload->id,
            'original_path' => $destinationRel,
            'meta' => ['filename' => $upload->filename],
        ]);

        $upload->update(['status' => 'processing']);

        GenerateImageVariants::dispatch($image);

        exec('php '.base_path('artisan').' queue:work --tries=3 --timeout=0 > /dev/null 2>&1 &');

        $product->ensurePrimaryImageFrom($image);

        return response()->json(['ok' => true, 'image_id' => $image->id]);
    }

    public function processStatus($uploadId)
    {
        $upload = Upload::findOrFail($uploadId);

        return response()->json(['ok' => true, 'status' => $upload->status, 'checksum' => $upload->checksum, 'meta' => $upload->meta]);
    }
}
