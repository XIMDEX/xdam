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
        }

        $resources = $resourceService->getAll(null, null, true);
        $count = 0;
        $reindexLOM = (!in_array('lom', $excludedCores));

        $reindexed = [];

        $this->withProgressBar($resources, function ($resource) use (&$count, $reindexLOM, &$reindexed, $solrService, $excludedCores, $solrVersion) {
            if (!isset($reindexed[$resource->type])) $reindexed[$resource->type] = 0;
            $reindexed[$resource->type]++;
            // if ( $reindexed[$resource->type] >= 10) return;

            $resourceCoreName = $solrService->getClientFromResource($resource)?->getEndpoint()?->getOptions()['core'] ?? null;
            if ($resourceCoreName === null) {
                $this->warn("Could not get core name for resource ID: " . $resource->id);
               // continue; // Skip to the next iteration of the loop
            }

            if (!in_array($resourceCoreName, $excludedCores) && $resourceCoreName !== null) {
                try {
                    $solrService->saveOrUpdateDocument($resource, $solrVersion, $reindexLOM);
                } catch (\Throwable $th) {
                    $this->error("\n Reindex of resource with ID " . $resource->id . " failed. Message Error: " . $th->getMessage());
                }
                $count++;
            }
        });
        $this->line("$count documents indexed");
    }
}
