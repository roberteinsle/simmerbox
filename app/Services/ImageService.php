<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    protected ?ImageManager $manager = null;

    protected function gdAvailable(): bool
    {
        return extension_loaded('gd');
    }

    protected function manager(): ImageManager
    {
        return $this->manager ??= new ImageManager(new Driver());
    }

    public function store(UploadedFile $file, string $directory = 'recipes'): string
    {
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $filename;

        if (!$this->gdAvailable()) {
            Storage::disk('public')->putFileAs($directory, $file, basename($path));
            return $path;
        }

        $image = $this->manager()->read($file->getPathname());
        $image->scaleDown(width: 1200);

        Storage::disk('public')->put($path, $image->toJpeg(85)->toString());

        // Create thumbnail
        $thumbPath = $directory . '/thumbs/' . $filename;
        $thumb = $this->manager()->read($file->getPathname());
        $thumb->cover(400, 300);

        Storage::disk('public')->put($thumbPath, $thumb->toJpeg(80)->toString());

        return $path;
    }

    public function delete(?string $path): void
    {
        if (!$path) {
            return;
        }

        Storage::disk('public')->delete($path);

        $directory = dirname($path);
        $filename = basename($path);
        Storage::disk('public')->delete($directory . '/thumbs/' . $filename);
    }
}
