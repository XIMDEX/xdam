<?php

namespace App\Services\Crawler;

use App\Models\CrawlerJob;
use Solarium\Core\Query\DocumentInterface;
use TSterker\Solarium\SolariumManager;

interface JobCrawlerServiceInteface
{
    /**
     * JobCrawlerService constructor.
     * @param SolariumManager $solarium
     * @param CrawlerJob $crawlerJob
     */
    public function __construct( SolariumManager $solarium, CrawlerJob $crawlerJob );

    /**
     * Set current solr core to crawler core
     */
    public function setCurrentCore();

    /**
     * Get first or last document in index
     * @param $firstDocument
     * @return false|DocumentInterface
     */
    public function getFirstLastDocumentInIndex( $firstDocument );

    /**
     * Get total of documents in index
     * @return int|null
     */
    public function getTotalDocumentsInIndex();

    /**
     * Get total documents in index since (date)
     * @param String $since
     * @return DocumentInterface[]
     */
    public function getIndexedDocumentsSince( string $since );

    /**
     * Set new record in crawlerjob
     * @param String $firstIndexedAt
     * @param String $lastIndexedAt
     * @param int $numFilesInIndex
     * @return mixed
     */
    public function newRecord( string $firstIndexedAt, string $lastIndexedAt, int $numFilesInIndex );

    /**
     * Get specific value from a indexed document
     * @param DocumentInterface $document
     * @return mixed
     */
    public function getIndexedAtValue( DocumentInterface $document );

    /**
     * Get Last indexed document
     * @return false|DocumentInterface
     */
    public function getLastIndexedDocument();

    /**
     * Get First indexed document
     * @return false|DocumentInterface
     */
    public function getFirstIndexedDocument();

    /**
     * Get last job in DB
     * @return mixed
     */
    public function getLastJob();

    /**
     * @return mixed
     */
    public function getCurrentCrawlerJob();

    /**
     * @param mixed $currentCrawlerJob
     */
    public function setCurrentCrawlerJob( $currentCrawlerJob ) : void;
}
