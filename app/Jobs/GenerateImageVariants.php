<?php

namespace App\Jobs;

use App\Models\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateImageVariants implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $imageId;

    public function __construct(Image $image)
    {
        $this->imageId = $image->id;
    }

    public function handle()
    {
        $image = Image::find($this->imageId);
        if (! $image) {
            return;
        }

        $upload = $image->upload;
        if (! $upload) {
            return;
        }

        $disk = Storage::disk('public');
        $originalRel = $image->original_path;

        try {
            $temp = tempnam(sys_get_temp_dir(), 'imgproc_');
            file_put_contents($temp, $disk->get($originalRel));

            $info = getimagesize($temp);
            if (! $info) {
                throw new \Exception('Invalid image file');
            }

            [$width, $height, $type] = $info;

            switch ($type) {
                case IMAGETYPE_JPEG:
                    $src = imagecreatefromjpeg($temp);
                    break;
                case IMAGETYPE_PNG:
                    $src = imagecreatefrompng($temp);
                    break;
                case IMAGETYPE_GIF:
                    $src = imagecreatefromgif($temp);
                    break;
                default:
                    throw new \Exception('Unsupported image type');
            }

            $sizes = [256, 512, 1024];
            $variants = [];

            foreach ($sizes as $size) {
                if ($width > $height) {
                    $newWidth = $size;
                    $newHeight = intval($height * ($size / $width));
                } else {
                    $newHeight = $size;
                    $newWidth = intval($width * ($size / $height));
                }

                $dst = imagecreatetruecolor($newWidth, $newHeight);

                // Preserve transparency for PNG/GIF
                if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
                    imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                }

                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                $variantDir = "uploads/variants/{$size}";
                if (! $disk->exists($variantDir)) {
                    $disk->makeDirectory($variantDir);
                }

                $variantPath = $variantDir.'/'.basename($originalRel);
                $tmpVar = tempnam(sys_get_temp_dir(), 'imgvar_');

                switch ($type) {
                    case IMAGETYPE_JPEG:
                        imagejpeg($dst, $tmpVar, 90);
                        break;
                    case IMAGETYPE_PNG:
                        imagepng($dst, $tmpVar);
                        break;
                    case IMAGETYPE_GIF:
                        imagegif($dst, $tmpVar);
                        break;
                }

                $disk->putFileAs($variantDir, $tmpVar, basename($originalRel));
                @unlink($tmpVar);
                imagedestroy($dst);

                $variants[$size] = $variantPath;
            }

            imagedestroy($src);
            @unlink($temp);

            // Update DB
            $image->update([
                'variant_256' => $variants[256] ?? null,
                'variant_512' => $variants[512] ?? null,
                'variant_1024' => $variants[1024] ?? null,
            ]);

            $upload->update(['status' => 'done']);

        } catch (\Exception $e) {
            Log::error('GenerateImageVariants GD error: '.$e->getMessage());
            $upload->update(['status' => 'failed']);
            @unlink($temp);
        }
    }
}
