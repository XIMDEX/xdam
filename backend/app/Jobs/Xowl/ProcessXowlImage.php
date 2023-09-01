<?php

namespace App\Jobs\Xowl;

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
    private string $capiton;
    private string $uuid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($xowlImageService,$media)
    {
        $this->xowlImageService = $xowlImageService;
        $this->media = $media;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $xowlImageService = $this->xowlImageService;
        $caption = $xowlImageService->getCaptionFromImage($this->media->getUrl());
        $xowlImageService->saveCaptionImage($caption,$this->media->id,$this->media->model_id);
    }

  
}
