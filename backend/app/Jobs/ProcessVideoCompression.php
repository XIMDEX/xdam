<?php

namespace App\Jobs;


use App\Services\Media\VideoCompressionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessVideoCompression implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    public function __construct( $task)
    {
        $this->task = $task;
    }

    public function handle(VideoCompressionService $compressionService)
    {
        try {
            $compressionService->compressVideo($this->task);
        } catch (\Exception $e) {
            // Handle exception (e.g., log the error or send a notification)
            echo "ERROR on conversion of " . $this->task->dest_path . ": " . $e->getMessage() . PHP_EOL;
        }
    }
}