<?php


namespace App\Services\Catalogue;


use App\Enums\ResourceType;
use App\Services\SolrService;

class CatalogueService
{
    /**
     * @var SolrService
     */
    private $solrService;

    /**
     * CatalogueService constructor.
     * @param SolrService $solrService
     * @param FacetManager $facetManager
     */
    public function __construct(SolrService $solrService)
    {
        $this->solrService = $solrService;
    }

    public function indexByType($pageParams, $sortParams, $facetsFilter)
    {
        return $this->solrService->paginatedQueryByFacet($pageParams, $sortParams, $facetsFilter);
    }

    public function exploreByType(ResourceType $type)
    {
       return $this->solrService->queryByFacet(['type' => $type->key]);
    }

    public function resetIndex()
    {
        $this->solrService->cleanSolr();
    }
}
