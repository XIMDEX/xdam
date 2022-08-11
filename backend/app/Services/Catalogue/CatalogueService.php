<?php


namespace App\Services\Catalogue;

use App\Models\Workspace;
use App\Services\Solr\SolrService;
use App\Services\OrganizationWorkspace\WorkspaceService;
use phpDocumentor\GraphViz\Exception;
use stdClass;

class CatalogueService
{
    /**
     * @var SolrService
     */
    private SolrService $solrService;

    /**
     * @var WorkspaceService
     */
    private WorkspaceService $workspaceService;

    /**
     * CatalogueService constructor.
     * @param SolrService $solrService
     * @param WorkspaceService $workspaceService
     */
    public function __construct(SolrService $solrService, WorkspaceService $workspaceService)
    {
        $this->solrService = $solrService;
        $this->workspaceService = $workspaceService;
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
        $defaultWorkspace = (string) $this->workspaceService->getDefaultWorkspace()->id;

        foreach ($response->facets as $facetKey => $facetValue) {
            if ($facetValue['key'] === 'workspaces') {
                foreach ($facetValue['values'] as $workspaceID => $workspaceValues) {
                    $newValues = array();

                    foreach ($workspaceValues as $key => $item) {
                        $newValues[$key] = $item;
                    }

                    $newValues['name'] = $this->getWorkspaceName($workspaceID);
                    $newValues['canBeEdit'] = $this->getWorkspaceCanBeEdit($defaultWorkspace, $workspaceID);
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

    private function getWorkspaceCanBeEdit($defaultWorkspaceID, $workspaceID)
    {
        return $defaultWorkspaceID != $workspaceID;
    }
}
