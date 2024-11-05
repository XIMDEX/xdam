<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class RenderService
{

    public function renderAvifImage(Media $media)
    {
        $originalExtension = pathinfo($media->getPath(), PATHINFO_EXTENSION);
        if (empty($originalExtension)) {
            $avifPath = $media->getPath() . '.avif';
        } else {
            $avifPath = $this->getConvertedPath($media->getPath(), 'avif');
        }
        $relativeAvifPath = explode('storage/app/', $avifPath)[1];
        $file = Storage::get($relativeAvifPath);
        $type = 'image/avif';

        return response($file, 200)->header('Content-Type', $type);
    }

    public function checkIfImageExists($mediaPath)
    {
        if (!Storage::exists($mediaPath)) {
            return false;
        }
        return true;
    }



    private function getRelativePath($mediaPath)
    {
        return explode('storage/app/', $mediaPath)[1];
    }

    public function generateAvif($path)
    {
        try {
            $img = Image::make(Storage::get($path))->encode('avif', 70);
            $avifPath = $this->getConvertedPath($path, 'avif');
            Storage::put($avifPath, (string) $img);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function checkAvif(Request $request)
    {
        $acceptHeader = $request->header('Accept', '');

        if (strpos($acceptHeader, 'image/avif') !== false) {
            return true;
        } else {
            return false;
        }
    }

    public function getConvertedPath($mediaPath, $targetExtension)
    {
        $originalExtension = pathinfo($mediaPath, PATHINFO_EXTENSION);

        $convertedPath = preg_replace('/\.' . preg_quote($originalExtension, '/') . '$/', '.' . $targetExtension, $mediaPath);

        return $convertedPath;
    }

    public function appendSizeToPath($mediaPath, $size)
    {
        $originalExtension = pathinfo($mediaPath, PATHINFO_EXTENSION);

        $directoryPath = dirname($mediaPath);

        $sizedPath = $directoryPath . '/__' . $size . '.' . $originalExtension;

        return  $sizedPath;
    }

    public function logAvifConversion($fileName, $originalSize, $newSize)
    { 
        $truncatedFileName = strlen($fileName) > 12 ? substr($fileName, 0, 9) . '...' : $fileName;
        $sizeDifference = $originalSize - $newSize;

        // Convert to MB
        $originalSizeMB = $originalSize / (1024 * 1024);
        $newSizeMB = $newSize / (1024 * 1024);
        $sizeDifferenceMB = $sizeDifference / (1024 * 1024);

        // Log individual file conversion
        $logMessage = sprintf(
            "File: %-12s Original: %10.2f MB AVIF: %10.2f MB Diff: %10.2f MB",
            $truncatedFileName,
            $originalSizeMB,
            $newSizeMB,
            $sizeDifferenceMB
        );

        // Also log to Laravel's logging system
        Log::channel('avif_conversion')->info($logMessage);
    }
}
