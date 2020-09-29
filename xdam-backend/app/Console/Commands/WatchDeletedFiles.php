<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Services\File\FileServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Finder;

class WatchDeletedFiles extends Command
{
    protected $signature = 'WatchDeletedFiles';
    protected $description = 'Watches paths for changes.';

    private $mapping = [];
    private $io_instance = null;
    private $semaphore = true;
    private $file;
    private $fileService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(File $file, FileServiceInterface $fileService)
    {
        parent::__construct();
        $this->file = $file;
        $this->fileService = $fileService;
    }

    /**
     * Execute the console command.
     * method that detects deleted files in dam's cache directory
     * @return int
     */
    public function handle() {
        $directoriesToScan = [];
        $finder = new Finder();
        $cacheDir = config('app.crawler_cache_dir', '');

        $finder->directories()->in($cacheDir);
        $directoriesToScan[] = $cacheDir;

        // for each of the folders in the cache directory get its path
        foreach ($finder as $file) {
            $directoriesToScan[] = $file->getRealPath();
        }

        // if have directoriesToScan
        if (!empty($directoriesToScan)) {

            $this->io_instance = inotify_init();
            stream_set_blocking($this->io_instance, 0);

            // for each directory add a inotify watcher
            foreach ($directoriesToScan as $directory) {
                $this->add_watch($directory);
            }

            while ($this->semaphore) {
                sleep(1);
                $events = inotify_read($this->io_instance);
                // If exists some deleted event iterate over them
                if (!empty($events)) {
                    foreach ($events as $event) {
                        $dir = $this->mapping[$event['wd']];
                        $filename = $event['name'];
                        $path = "$dir/$filename";

                        $filesDeleted = $this->file->where('crawler_path', $path)
                            ->get();

                        // for each deteled filed in inotify event go to process him
                        foreach($filesDeleted as $fileDeleted) {
                            $this->fileService->processFileDeleted($fileDeleted);
                        }
                    }
                }
            }

            Log::info("Shutting down gracefully.");
            foreach (array_keys($this->mapping) as $watch) {
                /* Remove watch from processed dirs */
                inotify_rm_watch($this->io_instance, $watch);
            }

            fclose($this->io_instance);
        }
    }

    private function add_watch($dir) {
        $watch = inotify_add_watch($this->io_instance, $dir, IN_DELETE  );
        $this->mapping[$watch] = $dir;
        Log::info("Watching " . $dir);
    }

}
