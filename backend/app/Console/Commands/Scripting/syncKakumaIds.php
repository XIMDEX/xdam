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

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set courses ids based on field data.id. This ID represents the course id in Kakuma backend';

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

        $resources = DamResource::where('type', 'course')->get();
        $count = 0;

        foreach ($resources as $resource) {
            $current_resource_id = $resource->id;

            $new_id = $this->update_dam_resource($resource);
            $this->update_table('category_dam_resource', 'dam_resource_id', $current_resource_id, $new_id);
            $this->update_table('dam_resource_uses', 'dam_resource_id', $current_resource_id, $new_id);
            $this->update_table('dam_resource_workspace', 'dam_resource_id', $current_resource_id, $new_id);
            $this->update_table('media', 'model_id', $current_resource_id, $new_id);

            //LAST STEP
            $solrService->saveOrUpdateDocument($resource); //indexed
            $count++;
            $this->line("$count documents updated and indexed");
        }

    }

    public function update_dam_resource($resource)
    {
        $data = !is_object($resource->data) ? json_decode($resource->data) : $resource->data;

        if (is_object($data)) {
            if (property_exists($data, 'description')) {
                $resource->id = $data->id;
                $resource->data = $data;
                $resource->save(); //updated
            }
        }

        return $data->id;
    }

    public function update_table($table_name, $column_to_update, $current_resource_id, $new_id)
    {
        $ress = DB::table($table_name)->where($column_to_update, $current_resource_id)->get();
        foreach ($ress as $res) {
            $res->dam_resource_id = $new_id;
            $res->save();
        }
    }
}
