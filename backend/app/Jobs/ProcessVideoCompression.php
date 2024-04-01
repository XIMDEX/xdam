<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\MediaConversion;
use App\Models\PendingVideoCompressionTask;
use App\Models\DamResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessVideoCompression implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PendingVideoCompressionTask $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $task = $this->task; // Use the task passed to the constructor
        $path = explode('/', $task->dest_path);
        echo "Conversion on " . $path[count($path) - 1] . " with ID " . $task->media_id . PHP_EOL;
        $output = [];
        $return_var = 0;
        $command = 'ffmpeg -i "' . $task->src_path . '" -vf scale=' 
                        . $task->resolution . ' -preset slow -crf 18 "'
                        . $task->dest_path . '"';
        exec($command, $output, $return_var);
        if ($return_var === 0) {
            MediaConversion::create([
                'media_id' => $task->media_id,
                'file_type' => $task->media()->pluck('mime_type')[0],
                'file_name' => $path[count($path) - 1],
                'file_compression' => $task->media_conversion_name_id,
                'resolution' => $task->resolution
            ]);
            $media = Media::where('id', $task->media_id)->first();
            $resource = DamResource::where('id', $media->model_id)->first();
            // Assuming $this->solr->saveOrUpdateDocument($resource); is handled elsewhere or injected
            $task->delete();
        } else {
            echo "ERROR on conversion of " . $task->dest_path . PHP_EOL;
        }
    }
}