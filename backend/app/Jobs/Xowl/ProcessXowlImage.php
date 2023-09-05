<?php

namespace App\Jobs\Xowl;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        $xowlImageService = $this->xowlImageService;
        $caption = $xowlImageService->getCaptionFromImage($this->mediaService->getMediaURL(new Media(), $this->media->id));
        $xowlImageService->saveCaptionImage($caption,$this->media->id,$this->media->model_id);
    }

  
}
