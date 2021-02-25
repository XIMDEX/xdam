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
    protected $signature = 'solr:reindex';

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
        Artisan::call('solr:clean');

        $resources = $resourceService->getAll();
        $count = 0;
        foreach ($resources as $resource) {
            $solrService->saveOrUpdateDocument($resource);
            $count++;
        }
        $this->line("$count documents indexed");
    }
}
