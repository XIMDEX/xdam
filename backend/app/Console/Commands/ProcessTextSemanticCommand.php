<?php

namespace App\Console\Commands;

use App\Services\ExternalApis\Xowl\XtagsCleaner;
use App\Services\ExternalApis\XowlTextService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use stdClass;

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
         $object = new stdClass();
         $object->uuid="";
         $files = Storage::allFiles('public');
         $finalFiles = [];
        foreach ($files as $file) {
          if(isset(pathinfo($file)['extension']) && (pathinfo($file)['extension']==='txt' || pathinfo($file)['extension']==='pdf') ){
               $finalFiles[] = $file;
          }
        }
        var_dump(basename(dirname($finalFiles[0]).".json"));
        if (!Storage::disk('semantic')->exists(basename(dirname($finalFiles[0]).".json"))) {
            var_dump(dirname($finalFiles[0].".json"));
            $result = $this->getSemanticData($finalFiles[0]);
            Storage::disk('semantic')->put(basename(dirname($finalFiles[0])).".json", json_encode($result));
        }
     
        //$petition = new XowlTextService(basename(dirname($finalFiles[0])));
      //  $petition->setFile($finalFiles[0],basename($finalFiles[0]));
      //  $result = $petition->;
       // $test = new XowlTextService();
       //  $file = Storage::path($files); 
       //  $file = Storage::get($files);
         //$passed = $this->get
         //$file22 = new \Symfony\Component\HttpFoundation\File\File($file);
         //$file = new UploadedFile($file,basename(dirname($file)));
         //$object->uuid=basename(dirname($file));
         
         //var_dump($this->getSemanticData($object,$file));
         echo("hello world\n");
        // var_dump($file);
        // $file = new \Symfony\Component\HttpFoundation\File\File($files);
         

    }

    private function getSemanticData($file){
        $xowlText = new XowlTextService(basename(dirname($file)));
        $xowlText->setFile(Storage::path($file),basename($file));
        $dataResult = $xowlText->getDataOwlFromFile($file);
        if($dataResult->status !=='FAIL'){
            $cleaner = new XtagsCleaner($dataResult->data->xtags,$dataResult->data->xtags_interlinked);
            return $cleaner->getProcessedXtags();
     
        }
    }
}
