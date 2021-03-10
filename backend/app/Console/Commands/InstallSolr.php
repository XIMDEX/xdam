<?php

namespace App\Console\Commands;

use App\Services\Solr\SolrConfig;
use Exception;
use Illuminate\Console\Command;

class InstallSolr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solr:install';

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
     * @param SolrConfig $solrConfig
     * @return bool
     */
    public function handle(SolrConfig $solrConfig)
    {
        try {
            $this->line($solrConfig->install());
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        return true;
    }
}
