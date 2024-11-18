<?php

namespace App\Console\Commands;

use App\Services\ResourceService;
use App\Services\Solr\SolrService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ReIndexSolr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solr:reindex {--exclude=*} {--solrVersion=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'index the resources in each of the solr instances';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle(SolrService $solrService, ResourceService $resourceService)
    {
        $excludedCores = $this->option('exclude');
        $solrVersion = $this->option('solrVersion');
        /*
        if (count($excludedCores) > 0) {
            $toExclude = '';

            foreach ($excludedCores as $core) {
                $auxCore = $solrService->getCoreNameVersioned($core, $solrService->getCoreVersion(($solrVersion)));
                $toExclude .= ' --exclude='.$auxCore ;
            }

            $fullCommand = 'solr:clean' . $toExclude . ' --fromReindex=true';
            $fullCommand .= ($solrVersion === null || $solrVersion === '' ? '' : (' --solrVersion=' . $solrVersion));
            Artisan::call($fullCommand);
        } else {
            $command = 'solr:clean --fromReindex=true';
            $command .= ($solrVersion === null || $solrVersion === '' ? '' : (' --solrVersion=' . $solrVersion));
            Artisan::call($command);
        }*/

        $resources = $resourceService->getAll(null, null, true);
        $count = 0;
        //    $reindexLOM = (!in_array('lom', $excludedCores));

        $reindexed = [];

        $this->withProgressBar($resources, function ($resource) use (&$count, &$reindexed, $solrService, $excludedCores, $solrVersion) {
            if (!isset($reindexed[$resource->type])) $reindexed[$resource->type] = 0;
            $reindexed[$resource->type]++;
            // if ( $reindexed[$resource->type] >= 10) return;
            $resourceCoreName = $solrService->getClientFromResource($resource)->getEndpoint()->getOptions()['core'];

            if (!in_array($resourceCoreName, $excludedCores) && $resourceCoreName !== null) {
                try {
                    if (!$solrService->documentExists($resource, $solrVersion)) {
                        $lom = $this->transformAttributes($resource->lom()->first()->toArray());
                        $resource->lom = $lom;
                        $solrService->saveOrUpdateDocument($resource, $solrVersion);
                    }
                } catch (\Throwable $th) {
                    $this->error("\n Reindex of resource with ID " . $resource->id . " failed. Message Error: " . $th->getMessage());
                    // continue with reindex
                }
                $count++;
            }
        });
        $this->line("$count documents indexed");
    }

    private function transformAttributes($attributes)
    {
        $transformed = [];

        foreach ($attributes as $key => $value) {
            // Match the pattern general_1_identifier
            if (preg_match('/^(\w+)_(\d+)_(\w+)$/', $key, $matches)) {
                $section = $matches[1]; // e.g., "general"
                $index = $matches[2];   // e.g., "1"
                $field = $matches[3];   // e.g., "identifier"

                // Construct the base key
                $baseKey = "lom.$index.$field";

                // If value is JSON encoded array, decode it
                if (is_string($value) && is_array(json_decode($value, true))) {
                    $value = json_decode($value, true);
                }

                // Check if the value is a nested array
                if (is_array($value)) {
                    foreach ($value as $subIndex => $subValue) {
                        if (is_array($subValue)) {
                            foreach ($subValue as $subKey => $subFieldValue) {
                                // Construct the new key for nested values
                                $newKey = "$baseKey.$subIndex.$subKey";
                                if($subFieldValue)$transformed[$newKey] = $subFieldValue;
                            }
                        } else {
                            // Handle case where the sub-value is not an array
                            $newKey = "$baseKey.$subIndex";
                            if($subValue)$transformed[$newKey] = $subValue;
                        }
                    }
                }
            }
        }

        return $transformed;
    }
}
