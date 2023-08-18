<?php

namespace App\Jobs;

use App\Services\ExternalApis\Xowl\XtagsCleaner;
use App\Services\ExternalApis\XowlTextService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;


class ProcessXowlDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $uuid;
    private $path;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uuid,$path,$uuidParent)
    {
        $this->uuid = $uuid;
        $this->path = $path;
        $this->uuidParent = $uuidParent;
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
        $xowlText = new XowlTextService($this->uuid); //dependency 
        $xowlText->setFile($this->path,$this->uuid);
        $dataResult = $xowlText->getDataOwlFromFile($this->uuid);
        if($dataResult->status !=='FAIL'){
            $cleaner = new XtagsCleaner($dataResult->data->xtags,$dataResult->data->xtags_interlinked);
            return $cleaner->getProcessedXtags(); 
        }
    }

    private function save(){
        if (!Storage::disk('semantic')->exists($this->uuid.".json")) {
            $result = $this->getSemanticData();
            Storage::disk('semantic')->put($this->uuid.".json", json_encode($result));
        }
    }
}
