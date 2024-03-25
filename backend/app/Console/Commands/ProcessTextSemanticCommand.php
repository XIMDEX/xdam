<?php

namespace App\Console\Commands;

use App\Enums\MediaType;
use App\Http\Resources\ResourceResource;
use App\Jobs\Xowl\ProcessXowlDocument;
use App\Services\ResourceService;
use Illuminate\Console\Command;

class ProcessTextSemanticCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:text';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add semantic data to all documents(txt,pdf)';

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
    public function handle(ResourceService $resourceService)
    {
        if(env('XOWL_AI')){
            $resources = $resourceService->getAll('document');
            foreach ($resources as $media) {
                $media2 = $media->getMedia('File');
                foreach ($media2 as $media3) {
                    if (pathinfo($media3->getPath())['extension'] === "pdf" || pathinfo($media3->getPath())['extension'] === "txt") {
                        $file = new ResourceResource($media3);
                        $path = $media3->getPath();
                        ProcessXowlDocument::dispatch($file,  $path);
                    }
                }
            }
        }
        
    }
}
