<?php

namespace App\Console\Commands;

use App\Models\PendingVideoCompressionTask;
use Illuminate\Console\Command;

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
     * @return void
     */
    public function handle()
    {
        $tasks = PendingVideoCompressionTask::all();
        $tasks->each(function($task) {
            $command = "ffmpeg -i " . $task->getSrcPath() . " -vf scale=" 
                            . $task->getResolution() . " -preset slow -crf 18 "
                            . $task->getDestPath();
            exec($command);
            $task->delete();
        });
    }
}
