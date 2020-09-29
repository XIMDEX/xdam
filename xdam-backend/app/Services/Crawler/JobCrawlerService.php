<?php


namespace App\Services\Crawler;


use App\Enums\CrawlerJobStatus;
use App\Models\CrawlerJob;
use Solarium\Core\Query\DocumentInterface;
use TSterker\Solarium\SolariumManager;

class JobCrawlerService implements JobCrawlerServiceInterface
{
    /**
     * store the Apache solr core for the crawler
     * @var string
     */
    protected $crawlerCore;
    /**
     * @var CrawlerJob
     */
    private $crawlerJob;
    /**
     * @var SolariumManager
     */
    private $solarium;

    private $currentCrawlerJob;


    /**
     * JobCrawlerService constructor.
     * @param SolariumManager $solarium
     * @param CrawlerJob $crawlerJob
     */
    public function __construct(SolariumManager $solarium, CrawlerJob $crawlerJob)
    {
        $this->crawlerJob = $crawlerJob;
        $this->solarium = $solarium;
    }

    /**
     * Set current solr core to crawler core
     */
    public function setCurrentCore(){
        $this->crawlerCore = config('app.solr_core_crawler', 'crawler');
        if($this->solarium->getEndpoint()->getCore() != $this->crawlerCore) {
            $this->solarium->getEndpoint()->setCore($this->crawlerCore);
        }
    }

    /**
     * Get first or last document in index
     * @param $firstDocument
     * @return false|DocumentInterface
     */
    public function getFirstLastDocumentInIndex( $firstDocument){
        $this->setCurrentCore();
        $query = $this->solarium->createSelect();
        $sort = $query::SORT_DESC;
        if ($firstDocument){
            $sort = $query::SORT_ASC;
        }
        $query->addSort('indexed_at', $sort);
        $query->setStart(0)->setRows(1);
        $lastDocument = $this->solarium->select($query)->getDocuments();

        if (empty($lastDocument)) {
            return false;
        } else {
            return $lastDocument[0];
        }
    }

    /**
     * Get total of documents in index
     * @return int|null
     */
    public function getTotalDocumentsInIndex(){
        $this->setCurrentCore();
        $query = $this->solarium->createSelect();
        return $this->solarium->select($query)->getNumFound();
    }

    /**
     * Get total documents in index since (date)
     * @param String $since
     * @return DocumentInterface[]
     */
    public function getIndexedDocumentsSince(String $since) {
        $this->setCurrentCore();
        $query = $this->solarium->createSelect();
        $queryText = "indexed_at:[" . $since . " TO NOW]";
        $query->setQuery($queryText);
        return $this->solarium->select($query)->getDocuments();
    }

    /**
     * Set new record in crawlerjob
     * @param String $firstIndexedAt
     * @param String $lastIndexedAt
     * @param int $numFilesInIndex
     * @return mixed
     */
    public function newRecord(String $firstIndexedAt, String $lastIndexedAt, int $numFilesInIndex){
        $newCrawlerJob = new $this->crawlerJob;
        $newCrawlerJob->first_indexed_at = $firstIndexedAt ? $firstIndexedAt : $this->getFirstIndexedDocument();
        $newCrawlerJob->last_indexed_at = $lastIndexedAt ? $lastIndexedAt : $this->getLastIndexedDocument();
        $newCrawlerJob->numfilesinindex = $numFilesInIndex;
        $newCrawlerJob->save();

        $this->setCurrentCrawlerJob($newCrawlerJob);
        return $newCrawlerJob;
    }


    /**
     * Get specific value from a indexed document
     * @param DocumentInterface $document
     * @return mixed
     */
    public function getIndexedAtValue(DocumentInterface $document) {
        $fields = $document->getFields();
        return $fields['indexed_at'][0];
    }

    /**
     * Get Last indexed document
     * @return false|DocumentInterface
     */
    public function getLastIndexedDocument() {
        return $this->getFirstLastDocumentInIndex(false);
    }

    /**
     * Get First indexed document
     * @return false|DocumentInterface
     */
    public function getFirstIndexedDocument() {
        return $this->getFirstLastDocumentInIndex(true);
    }

    /**
     * Get last job in DB
     * @return mixed
     */
    public function getLastJob() {
        if (null == $this->currentCrawlerJob){
            $this->currentCrawlerJob = $this->crawlerJob->latest()->first();
        }
        return $this->currentCrawlerJob;
    }



    /**
     * @return mixed
     */
    public function getCurrentCrawlerJob()
    {
        return $this->currentCrawlerJob;
    }

    /**
     * @param mixed $currentCrawlerJob
     */
    public function setCurrentCrawlerJob( $currentCrawlerJob ) : void
    {
        $this->currentCrawlerJob = $currentCrawlerJob;
    }
}
