<?php

namespace App\Services\Crawler;

use App\Services\File\FileServiceInterface;

interface getFilesFromCrawlerServiceInteface
{
    /**
     * getFilesFromCrawlerService constructor.
     * @param FileServiceInterface $fileService
     * @param JobCrawlerServiceInterface $jobCrawler
     */
    public function __construct( FileServiceInterface $fileService, JobCrawlerServiceInterface $jobCrawler );

    public function __invoke();
}
