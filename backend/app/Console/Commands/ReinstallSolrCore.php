<?php

namespace App\Console\Commands;

use App\Services\Solr\SolrConfig;
use Illuminate\Console\Command;

class ReinstallSolrCore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solr:reinstall {--core=*} {--all}';

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
     * @return int
     */
    public function handle(SolrConfig $solrConfig)
    {
        $cores = $this->option('core');
        $all = $this->option('all');
        $result = '';
        $a = config('solarium.connections');

        if($all) {
            foreach ($a as $core => $cv) {
                $cores[] = $core;
            }
        }

        foreach ($cores as $core) {
            $result .= $solrConfig->reinstallCore($core) . PHP_EOL;
        }
        $this->line($result);
    }
}
