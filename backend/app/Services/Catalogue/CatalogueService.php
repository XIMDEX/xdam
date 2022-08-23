<?php


namespace App\Services\Catalogue;


use App\Services\Solr\SolrService;
use phpDocumentor\GraphViz\Exception;
use stdClass;
use App\Utils\Texts;

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
        $result = $this->solrService->paginatedQueryByFacet($pageParams, $sortParams, $facetsFilter, $collection);
        for ($i = 0; $i < count($result->facets); $i++) {
            $facet = $result->facets[$i];
            $facet['label'] = Texts::web($facet['label']);
            $result->facets[$i] = $facet;
        }
        return $result;
    }
}
