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


    public function addDocumentToQueue($id,$mediaFiles)
    {
        $regex = '/\.(' . implode('|', $this->documentExtensions) . ')$/';
        foreach ($mediaFiles as $media) {
            $files = Storage::allFiles("public/{$media->id}");
            $filtered_files = array_filter($files, function ($file)  use ($regex) {
                return preg_match($regex, $file);
            });
            $this->dispatchJobs($filtered_files,$id);
        }
    }

    private function dispatchJobs($files, $id)
    {
       foreach ($files as $file) {
         ProcessXowlDocument::dispatch($id,Storage::path($file));
       }
    }
}
