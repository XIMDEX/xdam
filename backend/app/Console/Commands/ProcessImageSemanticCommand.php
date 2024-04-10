<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Resources\ResourceResource;
use App\Jobs\Xowl\ProcessXowlImage;
use App\Models\Media;
use App\Services\MediaService;
use \App\Services\ExternalApis\Xowl\XowlImageService;
use App\Services\ResourceService;

class ProcessImageSemanticCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:image';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add semantic data to all images';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ResourceService $resourceService, XowlImageService  $xowlImageService, MediaService $mediaService)
    {
        if(env('XOWL_AI')){
            $resources = $resourceService->getAll('document');
            foreach ($resources as $media) {
                $media2 = $media->getMedia('File'); //['jpg', 'jpeg', 'png']
                foreach ($media2 as $media3) {
                    if (pathinfo($media3->getPath())['extension'] === "jpg" || pathinfo($media3->getPath())['extension'] === "jpeg" || pathinfo($media3->getPath())['extension'] === "png") {
                        $file = new ResourceResource($media3);
                        ProcessXowlImage::dispatch($xowlImageService,$file,  $mediaService);
                    }
                }
            }
        }
        
    }
}
