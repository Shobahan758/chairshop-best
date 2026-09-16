<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductImageOptimizer
{
    private const MAX_BYTES = 40 * 1024;

    private const MAX_DIMENSION = 1600;

    public function store(UploadedFile $file): string
    {
        $source = imagecreatefromstring($file->getContent());

        if ($source === false) {
            throw new RuntimeException('The uploaded image could not be processed.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, self::MAX_DIMENSION / max($sourceWidth, $sourceHeight));
        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));
        $quality = 82;
        $contents = '';

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $canvas = imagecreatetruecolor($width, $height);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

            ob_start();
            imagewebp($canvas, null, $quality);
            $contents = (string) ob_get_clean();
            imagedestroy($canvas);

            if (strlen($contents) <= self::MAX_BYTES) {
                break;
            }

            $width = max(80, (int) round($width * 0.82));
            $height = max(80, (int) round($height * 0.82));
            $quality = max(25, $quality - 4);
        }

        imagedestroy($source);

        if ($contents === '' || strlen($contents) > self::MAX_BYTES) {
            throw new RuntimeException('The uploaded image could not be compressed to 40 KB.');
        }

        $path = 'products/'.Str::uuid().'.webp';

        if (! Storage::disk('public')->put($path, $contents)) {
            throw new RuntimeException('The optimized image could not be stored.');
        }

        return '/storage/'.$path;
    }

    public function delete(?string $imageUrl): void
    {
        if ($imageUrl && str_starts_with($imageUrl, '/storage/products/')) {
            Storage::disk('public')->delete(str($imageUrl)->after('/storage/')->toString());
        }
    }
}
