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
        $regex = '/\.(' . implode('|', ['jpg', 'jpeg', 'png', 'gif']) . ')$/';
        foreach ($mediaFiles as $media) {
            $files = Storage::allFiles("public/{$media->id}");
            $imageFiles = array_filter($files, function ($file)  use ($regex) {
                return preg_match($regex, $file);
            });
            foreach ($imageFiles as $imageFile) {
                ProcessXowlImage::dispatch($xowlImageService,$media); //injectar xowlimage, mediaurl y media
            }
        }
        
    }

    private function dispatchDocumentJobs($files, $media)
    {
        foreach ($files as $file) {
            ProcessXowlDocument::dispatch($media, Storage::path($file));
        }
    }

    /**
     * Get the caption for an image.
     *
     * @param string $mediaUrl The URL of the image.
     * @return string The caption for the image.
     */
    private function getCaptionFromImage(string $mediaUrl,\App\Services\ExternalApis\XowlImageService $xowlImageService)
    {
        try {
            return $caption = $xowlImageService->getCaptionImage($mediaUrl, env('BOOK_DEFAULT_LANGUAGE', 'en')) ?? "";
        } catch (\Exception $exc) {
            // failed captioning image -- continue process
        }
    }

    private function saveCaptionImage(string $caption, string $uuid)
    {
        $result = ["imageCaptionAi" => $caption];
        if (Storage::disk('semantic')->exists($uuid . "json")) {
            $file = json_decode(Storage::disk("semantic")->get($uuid . ".json"));
            $file->imageCaptionAi = $caption;
            $result = json_encode($file);
        }
        Storage::disk('semantic')->put($uuid . ".json", json_encode($result));
    }
}
