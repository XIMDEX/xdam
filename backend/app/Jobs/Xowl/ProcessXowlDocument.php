<?php

namespace App\Jobs\Xowl;

use App\Services\ExternalApis\Xowl\XtagsCleaner;
use App\Services\ExternalApis\Xowl\XowlTextService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;


class ProcessXowlDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $file;
    private $path;
    private $uuidParent;
    

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file,$path)
    {
        $this->file = $file;
        $this->path = $path;
        $this->uuidParent = $file->model_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->save();
    }

    private function getSemanticData(){
        $file = $this->file;
        $xowlText = new XowlTextService(  $this->uuidParent); //dependency 
        $xowlText->setFile($this->path,  $this->uuidParent);
        $dataResult = $xowlText->getDataOwlFromFile(  $this->uuidParent);
        if($dataResult->status !=='FAIL'){
            $cleaner = new XtagsCleaner($dataResult->data->xtags,$dataResult->data->xtags_interlinked,  $this->uuidParent);
            $cleaner = $cleaner->getProcessedXtags();
            $cleaner['file_name'] = $file->name; 
            $cleaner['file_extension'] = $file->extension;
            $cleaner['file_size'] = $file->size;
            return $cleaner; 
        }
    }

    private function save(){
        $file = $this->file;
        if (!Storage::disk('semantic')->exists(  $this->uuidParent."/".$file->id.".json")) {
            $result = $this->getSemanticData();
            Storage::disk('semantic')->put(  $this->uuidParent."/".$file->id.".json", json_encode($result));
        }
    }
}
