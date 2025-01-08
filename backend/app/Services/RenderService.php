<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

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
        $relativeAvifPath = explode(Storage::path(''), $avifPath)[1];
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

    public function generateAvif($path)
    {
        try {
            $manager = new ImageManager(new Driver());
            $image    = $manager->read(Storage::get($path));
            $avifImage = $image->toAvif(70);
            $avifPath = $this->getConvertedPath($path, 'avif');
            Storage::put($avifPath, (string) $avifImage);
            return true;
            //$img = Image::make(Storage::get($path))->encode('avif', 70);
            //$avifPath = $this->getConvertedPath($path, 'avif');
            //Storage::put($avifPath, (string) $img);
            //return true;
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
            list($browser, $version) = $this->getBrowserAndVersion();
            
            $version = (int) $version;
            
            if ($browser === 'Chrome' && $version >= 85) {
                return true;
            } else if ($browser === 'Firefox' && $version >= 93) {
                return true;
            } else if ($browser === 'Safari' && $version >= 14) {
                return true;
            } else if ($browser === 'Opera' && $version >= 72) {
                return true;
            } else if ($browser === 'Edge' && $version >= 85) {
                return true;
            } else if ($browser === 'Internet Explorer' && $version >= 11) {
                return true;
            }
        }
        
        return false;
    }
    
    private function getBrowserAndVersion() {
        $userAgent = $_SERVER['HTTP_USER_AGENT']; // Obtener el User-Agent del cliente
        $browser = null;
        $version = null;
        $versionnu = null;

        $browsers = [
            'Chrome' => 'Chrome',
            'Firefox' => 'Firefox',
            'Safari' => 'Safari',
            'Opera' => '(Opera|OPR)',
            'Edge' => 'Edg',
            'Internet Explorer' => 'MSIE|Trident',
        ];
    
        foreach ($browsers as $name => $pattern) {
            if (preg_match("/$pattern/i", $userAgent, $matches)) {
                $browser = $name;
                if ($browser === 'Safari') {
                    $regex = '/Version\/(\d+)\.\d+.*Safari\/(\d+)\.\d+\.\d+/';
                    preg_match($regex, $userAgent, $versionMatches);
                    if (!empty($versionMatches[1])) {
                        $version = $versionMatches[1];
                    }
                    break;
                } else {
                    preg_match("/{$matches[0]}\/([0-9\.]+)/", $userAgent, $versionMatches);
                    if (!empty($versionMatches[1])) {
                        $version = $versionMatches[1];
                    }
                    break;
                }
            }
        }
    
        return [$browser, $versionnu];
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
