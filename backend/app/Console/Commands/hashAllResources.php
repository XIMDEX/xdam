<?php

namespace App\Console\Commands;

use App\Models\DamResource;
use App\Services\CDNService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;

class hashAllResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hash:allresources {--collection=*} {--cdn=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate a hash for every resource';

    private CDNService $cdnService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CDNService $cdnService)
    {
        parent::__construct();
        $this->cdnService = $cdnService;    
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $cdnId = $this->option('cdn')[0] ?? null;
            $collectionId = $this->option('collection')[0] ?? null;

            if (empty($cdnId)) {
                $this->error('No CDN specified.');
                return 1;
            }

            $cdnExists = DB::table('cdns')->where('id', $cdnId)->exists();
            if (!$cdnExists) {
                $this->error('CDN not found.');
                return 1;
            }

            $resources = [];
            if (!empty($collectionId)) {
                $collectionExists = DB::table('collections')->where('id', $collectionId)->exists();
                if (!$collectionExists) {
                    $this->error('Collection not found.');
                    return 1;
                }


                $resources = DamResource::where('collection_id', $collectionId)->get();
            } else {
                $resources = DamResource::all();
            }

            $progressBar = new ProgressBar($this->output, $resources->count());

            foreach ($resources as $resource) {
                $collectionId = $resource->collection_id;
                $cdn = $this->cdnService->getCDNInfo($cdnId);
                $this->cdnService->generateDamResourceHash($cdn, $resource, $collectionId);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->info(' Hash generation completed successfully.');
            return 0;
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            return 1;
        }
    
    }
}
