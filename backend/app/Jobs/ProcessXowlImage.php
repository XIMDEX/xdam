<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessXowlImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }

    private function getCaptionFromImage(string $mediaUrl)
    {
        try {
            return $caption = $this->xowlImageService->getCaptionImage($mediaUrl, env('BOOK_DEFAULT_LANGUAGE', 'en')) ?? "";
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
