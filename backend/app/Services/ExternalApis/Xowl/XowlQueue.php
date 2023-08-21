<?php

namespace App\Services\ExternalApis\Xowl;

use App\Jobs\ProcessXowlDocument;
use Illuminate\Support\Facades\Storage;

class XowlQueue
{

    private array $documentExtensions = ['pdf', 'txt'];
    public function __construct()
    {
    }


    public function addDocumentToQueue($mediaFiles)
    {
        //sacar padre
        
        $regex = '/\.(' . implode('|', $this->documentExtensions) . ')$/';
        foreach ($mediaFiles as $media) {
            $files = Storage::allFiles("public/{$media->id}");
            $filtered_files = array_filter($files, function ($file)  use ($regex) {
                return preg_match($regex, $file);
            });
            $this->dispatchJobs($filtered_files, $media->id,$media->model_id);
        }
    }

    public function addImageToQueue(){
        
    }

    private function dispatchJobs($files, $id,$parent_id)
    {
        foreach ($files as $file) {
            ProcessXowlDocument::dispatch($id, Storage::path($file),$parent_id);
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
