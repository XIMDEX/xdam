<?php

namespace App\Services;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class RenderService
{
    private function renderImage(Request $request, $media, $mediaFileName, $size)
    {
        $path = $this->getRelativePath($media->getPath());
        $avifAvailable = $this->checkAvif($request);
        $originalExtension = pathinfo($media->getPath(), PATHINFO_EXTENSION);
        if (empty($originalExtension)) {
            $avifPath = $media->getPath() . '.avif';
        } else {
            $avifPath = $this->getAvifPath($media->getPath());
        }

        if (!$avifAvailable) {
            if (!Storage::exists($path)) {
                $this->generateAvif($media, $path, $mediaFileName);
            }
        }

        $relativeAvifPath = explode('storage/app/', $avifPath)[1];
        $file = Storage::get($relativeAvifPath);
        $type = 'image/avif';

        return response($file, 200)->header('Content-Type', $type);
    }

    private function getRelativePath($mediaPath)
    {
        return explode('storage/app/', $mediaPath)[1];
    }

    private function generateAvif($path)
    {
        try {
            $img = Image::make(Storage::get($path))->encode('avif', 70)->optimize();
            Storage::put($path . '.avif', (string) $img);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    private function checkAvif(Request $request)
    {
        $acceptHeader = $request->header('Accept', '');

        if (strpos($acceptHeader, 'image/avif') !== false) {
            return true;
        } else {
            return false;
        }
    }

    private function getAvifPath($mediaPath)
    {
        $originalExtension = pathinfo($mediaPath, PATHINFO_EXTENSION);

        // Only replace the last occurrence of the extension
        $avifPath = preg_replace('/\.' . preg_quote($originalExtension, '/') . '$/', '.avif', $mediaPath);

        return $avifPath;
    }
}
