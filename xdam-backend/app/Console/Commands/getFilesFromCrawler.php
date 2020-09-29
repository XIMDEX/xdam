<?php

namespace App\Console\Commands;

use App\Models\CrawlerJob;
use App\Services\Crawler\getFilesFromCrawlerServiceInterface;
use App\Services\File\FileIndexerServiceInterface;
use App\Services\File\FileServiceInterface;
use Illuminate\Console\Command;
use TSterker\Solarium\SolariumManager;

class getFilesFromCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getNewFilesFromCrawler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the new files indexed in solr from Apache Mainfoldcf Crawler';

    /**
     * Create a new command instance.
     *
     * @param SolariumManager $solarium
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Method that will be called from a cronjob task and that is responsible for reading from the crawler core and introducing the data in the dam core
     *
     * @param getFilesFromCrawlerServiceInterface $getFilesFromCrawler
     *
     * @param FileIndexerServiceInterface $fileIndexer
     * @return void
     */
    public function handle( getFilesFromCrawlerServiceInterface $getFilesFromCrawler, FileIndexerServiceInterface $fileIndexer)
    {
        /** @var callable $getFilesFromCrawler */
        // first get all the files from the crawler core
        $getFilesFromCrawler();
        // then iterate over the file table and index the files in the core dam
        $fileIndexer->indexFilesInDam();
    }
}
