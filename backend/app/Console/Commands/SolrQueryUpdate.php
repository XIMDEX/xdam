<?php

namespace App\Console\Commands;

use App\Services\ResourceService;
use App\Services\Solr\SolrService;
use Illuminate\Console\Command;

class SolrQueryUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solr:update {--query=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Solr elements by query';

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
    public function handle(SolrService $solrService, ResourceService $resourceService)
    {
        if (null == $this->option('query')) {
            $this->error("You must specify a query with '--query' option");
        } else {    
            $resources = $resourceService->queryFilter($this->option('query'));
            $count = 0;
            foreach ($resources as $resource) {
                $solrService->saveOrUpdateDocument($resource);
                $count++;
            }

            $this->line("$count documents updated");
        }
    }
}
