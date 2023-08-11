<?php

namespace App\Console\Commands;

use App\Services\ExternalApis\Xowl\XtagsCleaner;
use App\Services\ExternalApis\XowlTextService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
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
         $files = Storage::allFiles();
         $finalFiles = [];
        foreach ($files as $file) {
           // var_dump($file);
          if(isset(pathinfo($file)['extension']) && (pathinfo($file)['extension']==='txt' || pathinfo($file)['extension']==='pdf') ){
            if(new UploadedFile($file,basename(dirname($file)))!==null)$finalFiles[] = new UploadedFile($file,basename(dirname($file)));
          }
        }
        var_dump($finalFiles);
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

    private function getSemanticData($description,$file){
        $xowlText = new XowlTextService();
        $dataResult = $xowlText->getDataOwlFromFile($description,$file);
        var_dump(  $dataResult );
        if($dataResult->status !=='FAIL'){
            $cleaner = new XtagsCleaner($dataResult->data->xtags,$dataResult->data->xtags_interlinked);
            return $cleaner->getProcessedXtags();
     
        }
    }
}
