<?php

namespace App\Console\Commands;

use App\Http\Controllers\CDNController;
use App\Models\DamResource;
use App\Services\CDNService;
use Illuminate\Console\Command;

class hashAllResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hash:allresources';

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
        //$cdnController = new CDNController() ;
        $ids = DamResource::All();
        foreach ($ids as $id) {
            echo $id;
        }
        $cdn = $this->cdnService->getCDNInfo($request->cdn_code)
        $this->cdnService->generateDamResourceHash($cdn, $resource, $request->collection_id);
       // $cdnController->createCDNResourceHash();
    }
}
