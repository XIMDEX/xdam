<?php

namespace App\Jobs;

use App\Services\ExternalApis\Xowl\XtagsCleaner;
use App\Services\ExternalApis\XowlTextService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use stdClass;

class ProcessXowlDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $file;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->save($this->file);
        /*$files = Storage::allFiles('public');
        foreach ($files as $file) {
          if(isset(pathinfo($file)['extension']) && (pathinfo($file)['extension']==='txt' || pathinfo($file)['extension']==='pdf') ){
               $this->save($file);
          }
        }*/
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

    private function save($file){
        if (!Storage::disk('semantic')->exists(basename(dirname($file).".json"))) {
            var_dump(dirname($file.".json"));
            $result = $this->getSemanticData($file);
            Storage::disk('semantic')->put(basename(dirname($file)).".json", json_encode($result));
        }
    }
}
