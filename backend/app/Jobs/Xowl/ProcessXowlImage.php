<?php

namespace App\Jobs\Xowl;

use App\Models\Media;
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

    private $xowlImageService;
    private  $media;
    private  $mediaService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($xowlImageService,$media,$mediaService)
    {
        $this->xowlImageService = $xowlImageService;
        $this->media = $media;
        $this->mediaService = $mediaService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $urlMedia = $this->mediaService->getMediaURL(new Media(), $this->media->model_id);
        $caption = $this->getCaptionFromImage($urlMedia);
        $this->save($caption);
    }

    private function getCaptionFromImage($url){
        $result = ["imageCaptionAi" => ""];
        $caption = $this->xowlImageService->getCaptionFromImage("https://cf.bstatic.com/xdata/images/hotel/max1024x768/200579300.jpg?k=ad127946b5410f06fa1ccd78af7f635606ab71782a855936f43b52a0dc52e7e6&o=&hp=1");
        if (isset($caption)) $result = ["imageCaptionAi" => $caption,"vocabulary" => "AiImageCaption"];
        return $result;
    }

    private function save($result){
        if (!Storage::disk('semantic')->exists($this->media->model_id . "/" . $this->media->id . ".json")){
            Storage::disk('semantic')->put($this->media->model_id . "/" . $this->media->id . ".json", json_encode($result));
        }
    }

  
}
