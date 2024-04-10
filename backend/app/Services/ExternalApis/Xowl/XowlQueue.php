<?php

namespace App\Services\ExternalApis\Xowl;

use App\Jobs\Xowl\ProcessXowlImage;
use App\Jobs\Xowl\ProcessXowlDocument;
use App\Services\ExternalApis\Xowl\XowlImageService;
use App\Services\MediaService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class XowlQueue
{
    //For the future, make a XowlQueue for each file type
    private array $documentExtensions = ['pdf', 'txt'];
    private  MediaService $mediaService;
    private  XowlImageService $xowlImageService;

    public function __construct(MediaService $mediaService, XowlImageService $xowlImageService )
    {
        $this->mediaService = $mediaService;    
        $this->xowlImageService = $xowlImageService;
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

    public function addImageToQueue($mediaFiles) 
    {
        $regex = '/\.(' . implode('|', ['jpg', 'jpeg', 'png']) . ')$/';
        foreach ($mediaFiles as $media) {
            $files = Storage::allFiles("public/{$media->id}");
            $imageFiles = array_filter($files, function ($file)  use ($regex) {
                return preg_match($regex, $file);
            });
            $this->dispatchImageJobs($imageFiles, $media);
        }
        
    }

    private function dispatchDocumentJobs($files, $media)
    {
        array_map(function($file) use ($media) {
            ProcessXowlDocument::dispatchAfterResponse($media, Storage::path($file));
        }, $files);        
    }

    private function dispatchImageJobs($imageFiles,$media){
        array_map(function() use ($media) {
            ProcessXowlImage::dispatchAfterResponse($this->xowlImageService, $media, $this->mediaService);
        }, $imageFiles);
    }

}
