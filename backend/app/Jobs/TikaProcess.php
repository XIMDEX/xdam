<?php

namespace App\Jobs;

use App\Models\DamResource;
use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TikaProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $resource_id;
    
    private $media_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DamResource $damResource, Media $media)
    {
        $this->resource_id = $damResource->id;
        $this->media_id = $media->id;
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
}
