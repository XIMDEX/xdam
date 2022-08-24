<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\DamResource;
use App\Models\MediaConversion;
use App\Models\PendingVideoCompressionTask;
use Illuminate\Console\Command;
use App\Services\Solr\SolrService;

class CompressVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compress:videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts the pending video compressions';

    private $solr;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SolrService $solr)
    {
        parent::__construct();
        $this->solr = $solr;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $tasks = PendingVideoCompressionTask::all();
        $tasks->each(function($task) {
            $path = explode('/', $task->dest_path);
            
            MediaConversion::create([
                'media_id' => $task->media_id,
                'file_type' => $task->media()->pluck('mime_type')[0],
                'file_name' => $path[count($path) - 1],
                'file_compression' => $task->media_conversion_name_id,
                'resolution' => $task->resolution
            ]);

            $media = Media::where('id', $task->media_id)->first();
            $resource = DamResource::where('id', $media->model_id)->first();
            $command = "ffmpeg -i " . $task->src_path . " -vf scale=" 
                            . $task->resolution . " -preset slow -crf 18 "
                            . $task->dest_path;
            exec($command);
            $this->solr->saveOrUpdateDocument($resource);
            $task->delete();
        });
    }
}
