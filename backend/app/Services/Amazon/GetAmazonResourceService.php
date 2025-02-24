<?php

namespace App\Services\Amazon;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;


class GetAmazonResourceService
{
    private Filesystem $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('s3');
    }

    public function getResource(string $s3Key): UploadedFile
    {
        $this->validateFileExists($s3Key);
        
       // $contentType = $this->disk->mimeType($s3Key);
        $contentLength = $this->disk->size($s3Key);
        $resourceContent = $this->disk->get($s3Key);

        $tempFilePath = $this->createTempFile($resourceContent);

        return $this->createUploadedFile($tempFilePath, $s3Key, 'image/jpeg', $contentLength);
    }

    private function validateFileExists(string $s3Key): void
    {
        if (!$this->disk->exists($s3Key)) {
            throw new FileNotFoundException("File not found in S3: {$s3Key}");
        }
    }

    private function createTempFile(string $content): string
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), 'S3File');
        file_put_contents($tempFilePath, $content);
        return $tempFilePath;
    }

    private function createUploadedFile(string $path, string $originalName, string $mimeType, int $size): UploadedFile
    {
        return new UploadedFile(
            $path,
            basename($originalName),
            $mimeType,
            $size,
            UPLOAD_ERR_OK,
            true
        );
    }
}