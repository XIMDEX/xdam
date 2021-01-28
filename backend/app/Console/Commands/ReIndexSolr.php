<?php

namespace App\Console\Commands;

use App\Services\ResourceService;
use App\Services\Solr\SolrService;
use Exception;
use Illuminate\Console\Command;

class ReIndexSolr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reindex:solr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index mysql content in Apache Solr';

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
        try {
            $solrService->ping();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $solrService->cleanSolr();
        $resources = $resourceService->getAll();
        $count = 0;
        foreach($resources as $resource)
        {
            $solrService->saveOrUpdateDocument($resourceService->prepareResourceToBeIndexed($resource));
            $count++;
        }
        echo "$count documents indexed";
    }
}
