<?php


namespace App\Services\Catalogue;

use App\Models\Workspace;
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
        return $this->formatSolrResponseWithWorkspaceInformation(
            $this->solrService->paginatedQueryByFacet(
                $pageParams, $sortParams, $facetsFilter, $collection
            )
        );
    }

    private function formatSolrResponseWithWorkspaceInformation($solrResponse)
    {
        $response = $solrResponse;

        foreach ($response->facets as $facetKey => $facetValue) {
            if ($facetValue['key'] === 'workspaces') {
                foreach ($facetValue['values'] as $workspaceID => $workspaceValues) {
                    $newValues = array();

                    foreach ($workspaceValues as $key => $item) {
                        $newValues[$key] = $item;
                    }

                    $newValues['name'] = $this->getWorkspaceName($workspaceID);
                    $response->facets[$facetKey]['values'][$workspaceID] = $newValues;
                }
            }
        }

        return $response;
    }

    private function getWorkspaceName($workspaceID)
    {
        $workspace = Workspace::where('id', $workspaceID)->first();

        if ($workspace === null)
            return '';

        return $workspace->name;
    }
}
