<?php


namespace App\Services\Crawler;


use App\Enums\CrawlerJobStatus;
use App\Services\File\FileServiceInterface;

class getFilesFromCrawlerService implements getFilesFromCrawlerServiceInterface
{

    /**
     * @var FileServiceInterface
     */
    private $fileService;
    /**
     * @var JobCrawlerServiceInterface
     */
    private $jobCrawlerService;


    /**
     * getFilesFromCrawlerService constructor.
     * @param FileServiceInterface $fileService
     * @param JobCrawlerServiceInterface $jobCrawler
     */
    public function __construct( FileServiceInterface $fileService, JobCrawlerServiceInterface $jobCrawler)
    {
        $this->fileService = $fileService;
        $this->jobCrawlerService = $jobCrawler;
    }


    public function __invoke() {

        $lastDocumentInIndex = $this->jobCrawlerService->getLastIndexedDocument();

        if (null == $lastDocumentInIndex) {
            return new \Exception('No documents in Index');
        }

        $totalDocumentsInIndex = $this->jobCrawlerService->getTotalDocumentsInIndex();
        $lastCrawlerJob = $this->jobCrawlerService->getLastJob();
        $firstIndexedDocument = $this->jobCrawlerService->getFirstIndexedDocument();
        $lastIndexedDocument = $this->jobCrawlerService->getLastIndexedDocument();
        $firstIndexedAt = $this->jobCrawlerService->getIndexedAtValue($firstIndexedDocument);
        $lastIndexedAt = $this->jobCrawlerService->getIndexedAtValue($lastIndexedDocument);


        if (null != $lastCrawlerJob) {
            // if there is any previous task, the first indexed is the last of the previous task
            $firstIndexedAt = $lastCrawlerJob->last_indexed_at;
        }

        // create the new crawker job
        $currentCrawlerJob = $this->jobCrawlerService->newRecord($firstIndexedAt, $lastIndexedAt, $totalDocumentsInIndex);

        // get all the documents that have been indexed since the last crawler job
        $newDocumentsFromLastIndexed = $this->jobCrawlerService->getIndexedDocumentsSince($firstIndexedAt);

        // set state of crawlerjob to processing
        $currentCrawlerJob->status = CrawlerJobStatus::Processing;

        // foreach new document process file
        foreach ($newDocumentsFromLastIndexed as $crawlerFile) {
            $this->fileService->processCrawlerFile($currentCrawlerJob, $crawlerFile);
            $currentCrawlerJob->numfilesprocessed++;
        }

        // set state of crawlerjob to done
        $currentCrawlerJob->status = CrawlerJobStatus::Done;

        $currentCrawlerJob->save();
        return 0;
    }
}
