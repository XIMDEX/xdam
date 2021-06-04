<?php

namespace App\Console\Commands\Scripting;


use App\Models\DamResource;
use App\Services\ResourceService;
use App\Services\Solr\SolrService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class syncKakumaIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncKakumaIds:start';

    private $db_schema;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set courses ids based on field data.id. This ID represents the course id in Kakuma database';

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
     * @param SolrService $solrService
     * @param ResourceService $resourceService
     * @return int
     * @throws \Exception
     */
    public function handle(SolrService $solrService)
    {
        if ($this->confirm('WARNING! Before proceed, check the foreign keys / relations of dam_resource, and handle it. Otherwise, relations between entities will be broken. Proceed?')) {
            if ($this->confirm('Want to update all resources or only one? (yes = update all) (no = enter the resource)')) {
                $resources = DamResource::where('type', 'course')->get();

                foreach ($resources as $resource) {
                    $this->updateResource($resource, $solrService);
                }
            } else {
                $rid = $this->ask('Enter resource id');
                $resource = DamResource::where(['type' => 'course', 'id' => $rid])->first();
                $this->updateResource($resource, $solrService);
            }
            echo 'finished' . PHP_EOL;
        }
    }

    public function updateResource($resource, $solrService)
    {
        $data = !is_object($resource->data) ? json_decode($resource->data) : $resource->data;

        if (is_object($data)) {
            if (property_exists($data, 'description')) {
                $newId = $data->id ?? null;
                if($newId) {
                    $solrService->deleteDocument($resource); //deleted from solr
                    $resource->id = $newId;
                    $resource->save(); //updated
                    $solrService->saveOrUpdateDocument($resource); //indexed
                    $this->line("$resource->id updated and indexed");
                } else {
                    $this->line($resource->id . 'has not id prop on data column');
                }
            }
        }
    }
}
