<?php

namespace App\Console\Commands;

use App\Services\Solr\SolrConfig;
use App\Services\Solr\SolrService;
use Illuminate\Console\Command;

class CleanSolr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solr:clean {--exclude=*} {--fromReindex=false} {--solrVersion=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @param SolrConfig $solrConfig
     * @param SolrService $solrService
     * @return int
     */
    public function handle(SolrConfig $solrConfig, SolrService $solrService)
    {
        $fromReindex = $this->option('fromReindex');
        $excludedCores = $this->option('exclude');
        $solrVersion = $this->option('solrVersion');
        $this->line($solrConfig->cleanDocuments($excludedCores, $fromReindex == 'true' ? 'reindex' : 'clean', $solrVersion));
        return 0;
    }
}
