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

    public function indexByCollection($pageParams, $sortParams, $facetsFilter, $collection): stdClass
    {
        return $this->solrService->paginatedQueryByFacet($pageParams, $sortParams, $facetsFilter, $collection);
    }
}
