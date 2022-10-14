<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetCoreNameVersioned extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:coreNameVersioned {--coreName=}';

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
    public function handle()
    {
        $coreName = $this->option('coreName');
        $solrVersion = env('SOLR_CORES_VERSION', '');
        
        if ($coreName === NULL) return '';

        $coreName .= ($solrVersion !== '' ? ('_' . $solrVersion) : '');
        echo $coreName;
        return 0;
    }
}
