<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicImageTest extends TestCase
{
    public function test_public_image_is_served_without_a_storage_link(): void
    {
        Storage::fake('public');
        $path = 'products/chair.png';
        Storage::disk('public')->put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));

        $response = $this->get('/storage/'.$path);

        $response->assertOk()->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Cache-Control', 'max-age=86400, public')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame(Storage::disk('public')->path($path), $response->baseResponse->getFile()->getPathname());
        Storage::disk('public')->assertExists($path);
    }

    public function test_missing_image_returns_not_found(): void
    {
        Storage::fake('public');

        $this->get('/storage/products/missing.webp')->assertNotFound();
    }

    public function test_non_image_public_files_are_not_served(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/script.php', '<?php echo "private";');
        Storage::disk('public')->put('products/disguised.png', '<?php echo "private";');

        $this->get('/storage/products/script.php')->assertNotFound();
        $this->get('/storage/products/disguised.png')->assertNotFound();
        Storage::disk('public')->assertCount('/products', 2);
    }

    public function test_private_storage_still_requires_a_signed_url(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('secret.txt', 'Private content');

        $this->get('/private-storage/secret.txt')->assertForbidden();
    }

    public function test_paths_outside_public_storage_are_not_served(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        Storage::disk('local')->put('private.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
        symlink(Storage::disk('local')->path('private.png'), Storage::disk('public')->path('outside.png'));

        $this->get('/storage/outside.png')->assertNotFound();
        $this->get('/storage/../private.png')->assertNotFound();
    }
}
