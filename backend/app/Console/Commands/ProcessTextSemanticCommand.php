<?php

namespace App\Console\Commands;

use App\Jobs\ProcessXowlDocument;
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
    protected $description = 'Add semantic data to all documents(txt,pdf)';

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
        $files = Storage::allFiles('public');
        foreach ($files as $file) {
            if (isset(pathinfo($file)['extension']) && (pathinfo($file)['extension'] === 'txt' || pathinfo($file)['extension'] === 'pdf')) {
                ProcessXowlDocument::dispatch(basename(dirname($file)),Storage::path($file));
            }
        }
    }
}
