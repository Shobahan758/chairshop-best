<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\CorruptedPathDetected;
use League\Flysystem\PathTraversalDetected;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicImageController extends Controller
{
    public function __invoke(string $path): BinaryFileResponse
    {
        $disk = Storage::disk('public');
        $root = realpath($disk->path(''));
        try {
            $image = realpath($disk->path($path));
        } catch (PathTraversalDetected|CorruptedPathDetected) {
            abort(404);
        }

        abort_unless($root !== false && $image !== false && str_starts_with($image, $root.DIRECTORY_SEPARATOR) && is_file($image), 404);
        abort_unless(in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true), 404);
        abort_unless(in_array(mime_content_type($image), ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'], true), 404);

        return response()->file($image, [
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
