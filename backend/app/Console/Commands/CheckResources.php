<?php

namespace App\Console\Commands;

use App\Enums\ResourceType;
use App\Models\Collection;
use App\Models\DamResource;
use App\Services\Solr\SolrService;
use Illuminate\Console\Command;

class CheckResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:resources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks the resources';

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
    public function handle(SolrService $solrService)
    {
        $resources = DamResource::all();

        foreach ($resources as $resource) {
            $solrService->saveOrUpdateDocument($resource);
        }
    }
}
