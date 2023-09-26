<?php

namespace App\Console\Commands;

use App\Http\Controllers\CDNController;
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
        //$cdnController = new CDNController() ;
        $cdnId = $this->option('cdn')[0];
        if ($cdnId) {
            $collection = $this->option('collection')[0];
            $resources  = [];
            var_dump($collection);
            $exists = DB::table('collections')->where('id',$collection)->exists();
            if ($collection) {
                if($exists) {
                    $resources = DamResource::where('collection_id', $collection)->get();
                }else{
                    echo "collection not found";
                }
            }else{
                $resources = DamResource::All();
            }
                $progressBar = new ProgressBar($this->output, $resources->count());
                $cdn = $this->cdnService->getCDNInfo($cdnId);

                foreach ($resources as $resource) {
                    if(!$collection) $collection = $resource->collection_id;
                    $this->cdnService->generateDamResourceHash($cdn, $resource, $collection);
                    $progressBar->advance();
                }
                $progressBar->finish();
        }else{
        echo("no cdn specified");
        }
    }
}
