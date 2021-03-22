<?php

namespace App\Console\Commands;

use App\Services\Solr\SolrConfig;
use Illuminate\Console\Command;

class CleanSolr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solr:clean {--exclude=*} {--fromReindex=false}';

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
     * @return int
     */
    public function handle(SolrConfig $solrConfig)
    {
        $fromReindex = $this->option('fromReindex');
        $excludedCores = $this->option('exclude');
        $this->line($solrConfig->cleanDocuments($excludedCores, $fromReindex == 'true' ? 'reindex' : 'clean'));
        return 0;
    }
}
