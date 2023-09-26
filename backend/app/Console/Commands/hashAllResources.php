<?php

namespace App\Console\Commands;

use App\Http\Controllers\CDNController;
use App\Models\DamResource;
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
    public function handle(CDNController $cdnController)
    {
        //$cdnController = new CDNController() ;
        $ids = DamResource::pluck('id');
        foreach ($ids as $id) {
            echo $id;
        }
       // $cdnController->createCDNResourceHash();
    }
}
