<?php

namespace App\Console\Commands;

use App\Services\Catalogue\CatalogueService;
use App\Services\ResourceService;
use App\Services\Solr\SolrService;
use Illuminate\Console\Command;

class InstallSolr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:solr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'command that checks the current solr installation and runs a check of cores and schemas';

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
     * @return int
     */
    public function handle(SolrService $solrService)
    {
        try {
            $solrService->solrServerIsReady();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        return true;
    }
}
