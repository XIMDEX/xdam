<?php

namespace App\Console\Commands;

use App\Services\ExternalApis\Xowl\XtagsCleaner;
use App\Services\ExternalApis\XowlTextService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProcessTextSemanticCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:text';

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
         $files = Storage::allFiles()[64];
         $file = Storage::path($files);
       //  $file = Storage::get($files);
         //$passed = $this->get
         //$file22 = new \Symfony\Component\HttpFoundation\File\File($file);
         $file = new \Symfony\Component\HttpFoundation\File\File($file);
         var_dump($file->getRealPath());

         echo("hello world\n");
        // var_dump($file);
        // $file = new \Symfony\Component\HttpFoundation\File\File($files);
         

    }

    private function GetSemanticData($description,$file){
        $xowlText = new XowlTextService();
        $dataResult = $xowlText->getDataOwlFromFile($description,$file);
        if($dataResult->status !=='FAIL'){
            $cleaner = new XtagsCleaner($dataResult->data->xtags,$dataResult->data->xtags_interlinked);
            return $cleaner->getProcessedXtags();
     
        }
    }
}
