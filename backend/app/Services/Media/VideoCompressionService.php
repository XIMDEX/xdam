<?php
namespace App\Services\Media;

use App\Models\MediaConversion;
use App\Models\Media;
use App\Models\DamResource;
use App\Services\Solr\SolrService;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class VideoCompressionService
{
    private SolrService $solr;
    public function __construct(SolrService $solr)
    {
        $this->solr = $solr;
    }

    public function compressVideo($task)
    {
        if(!file_exists($task->dest_path)){
            $path = explode('/', $task->dest_path);
            $command = [
                'ffmpeg', '-i', $task->src_path, '-vf', 'scale=' . $task->resolution,
                '-preset', 'slow', '-crf', '18', $task->dest_path
            ];
    
            $process = new Process($command);
            $process->run();
    
            // Executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
    
            // Save the conversion details to the database
            $this->saveConversionDetails($task, $path);
        }
      
    }

    protected function saveConversionDetails($task, $path)
    {
        $media = Media::where('id', $task->media_id)->first();
        MediaConversion::create([
            'media_id' => $task->media_id,
            'file_type' => $media->mime_type,
            'file_name' => $path[count($path) - 1],
            'file_compression' => $task->media_conversion_name_id,
            'resolution' => $task->resolution
        ]);

        $resource = DamResource::where('id', $media->model_id)->first();
        $this->solr->saveOrUpdateDocument($resource);
    }
}