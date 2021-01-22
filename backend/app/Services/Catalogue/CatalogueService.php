<?php


namespace App\Services\Catalogue;


use App\Services\Solr\SolrService;
use phpDocumentor\GraphViz\Exception;
use stdClass;

class CatalogueService
{
    /**
     * @var SolrService
     */
    private SolrService $solrService;

    /**
     * CatalogueService constructor.
     * @param SolrService $solrService
     */
    public function __construct(SolrService $solrService)
    {
        $this->solrService = $solrService;
    }

    public function throwErrorIfNotValidCollection($collection)
    {
        if (!$this->solrService->isValidCollection($collection)) {
            throw new Exception("The param is not a valid collection");
        }
    }

    public function indexByCollection($pageParams, $sortParams, $facetsFilter, $collection): stdClass
    {
        $this->throwErrorIfNotValidCollection($collection);
        return $this->solrService->paginatedQueryByFacet($pageParams, $sortParams, $facetsFilter, $collection);
    }

    public function exploreByCollection($collection): stdClass
    {
        $this->throwErrorIfNotValidCollection($collection);
        return $this->solrService->queryByFacet([], $collection);
    }

    public function resetIndex()
    {
        $this->solrService->cleanSolr();
    }

    public function checkSolr()
    {
        if ($this->solrService->solrServerIsReady()) {
            return true;
        }
        return false;
    }
}
