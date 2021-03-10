<?php

namespace App\Console\Commands\Maintenance;

use App\Enums\ResourceType;
use App\Services\ResourceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CourseTagsReindex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coursetags:reindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'go through the course type resources and extract the tags';

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
     * @param ResourceService $resourceService
     * @return int
     */
    public function handle(ResourceService $resourceService)
    {
        $resources = $resourceService->getAll(ResourceType::course);
        $count = 0;
        foreach ($resources as $resource) {
            $data = !is_object($resource->data) ? json_decode($resource->data) : $resource->data;
            if (is_object($data)){
                $resourceService->linkTagsFromJson($resource, $data);
                $count++;
            }
        }
        $this->line("$count resources checked");
    }
}
