<?php

namespace App\Services\ExternalApis\Xowl;

use App\Jobs\Xowl\ProcessXowlImage;
use App\Jobs\Xowl\ProcessXowlDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class XowlQueue
{

    private array $documentExtensions = ['pdf', 'txt'];
    public function __construct()
    {
    }


    public function addDocumentToQueue($mediaFiles)
    {
        $regex = '/\.(' . implode('|', $this->documentExtensions) . ')$/';
        foreach ($mediaFiles as $media) {
            $files = Storage::allFiles("public/{$media->id}");
            $filtered_files = array_filter($files, function ($file)  use ($regex) {
                return preg_match($regex, $file);
            });
            $this->dispatchDocumentJobs($filtered_files, $media);
        }
    }

    public function addImageToQueue($mediaFiles){

        $xowlImageService = new \App\Services\ExternalApis\Xowl\XowlImageService();
        $mediaService     = new \App\Services\MediaService();
        $regex = '/\.(' . implode('|', ['jpg', 'jpeg', 'png']) . ')$/';
        foreach ($mediaFiles as $media) {
            $files = Storage::allFiles("public/{$media->id}");
            $imageFiles = array_filter($files, function ($file)  use ($regex) {
                return preg_match($regex, $file);
            });
            foreach ($imageFiles as $imageFile) {
                ProcessXowlImage::dispatch($xowlImageService,$media,$mediaService); //injectar xowlimage, mediaurl y media
            }
        }
        
    }

    private function dispatchDocumentJobs($files, $media)
    {
        foreach ($files as $file) {
            ProcessXowlDocument::dispatch($media, Storage::path($file));
        }
    }

}
