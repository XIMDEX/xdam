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
    protected $signature = 'solr:reindex {--exclude=*} {--solrVersion=} {--force} {--avoidDuplicates}';

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
        $force = $this->option('force');
        $avoidDuplicates = $this->option('avoidDuplicates');

        if (count($excludedCores) > 0) {
            $toExclude = '';

            foreach ($excludedCores as $core) {
                $auxCore = $solrService->getCoreNameVersioned($core, $solrService->getCoreVersion(($solrVersion)));
                $toExclude .= ' --exclude=' . $auxCore ;
            }

            $fullCommand = 'solr:clean' . $toExclude . ' --fromReindex=true';
            $fullCommand .= ($solrVersion === null || $solrVersion === '' ? '' : (' --solrVersion=' . $solrVersion));
            Artisan::call($fullCommand);
        } else {
            $command = 'solr:clean --fromReindex=true';
            $command .= ($solrVersion === null || $solrVersion === '' ? '' : (' --solrVersion=' . $solrVersion));
            Artisan::call($command);
        }

        $resources = $resourceService->getAll();
        $total = count($resources);
        $count = 0;
        $reindexLOM = (!in_array('lom', $excludedCores));
        $this->output->progressStart($total);

        foreach ($resources as $resource) {
            $this->output->progressAdvance();
            $resourceCoreName = $solrService->getClientFromResource($resource)->getEndpoint()->getOptions()['core'];

            if (!in_array($resourceCoreName, $excludedCores) && $resourceCoreName !== null) {
                $solrService->saveOrUpdateDocument($resource, $solrVersion, $reindexLOM, $force, $avoidDuplicates);
                $count++;
            }
        }

        $this->output->progressFinish();
        $this->line("$count documents indexed");
    }
}
