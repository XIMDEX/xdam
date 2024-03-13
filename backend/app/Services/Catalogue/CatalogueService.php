<?php


namespace App\Services\Catalogue;

use App\Models\Collection;
use App\Models\Workspace;
use App\Services\Solr\SolrService;
use App\Services\OrganizationWorkspace\WorkspaceService;
use phpDocumentor\GraphViz\Exception;
use stdClass;
use App\Utils\Texts;
use App\Services\Solr\SolrConfig;


class CatalogueService
{
    /**
     * @var SolrService
     */
    private SolrService $solrService;
    
    /**
     * @var SolrConfig
     */
    private SolrConfig $solrConfig;

    /**
     * @var WorkspaceService
     */
    private WorkspaceService $workspaceService;

    /**
     * CatalogueService constructor.
     * @param SolrService $solrService
     * @param WorkspaceService $workspaceService
     */
    public function __construct(SolrService $solrService, WorkspaceService $workspaceService, SolrConfig $solrConfig)
    {
        $this->solrService = $solrService;
        $this->workspaceService = $workspaceService;
        $this->solrConfig = $solrConfig;
    }

    public function indexByCollection($pageParams, $sortParams, $facetsFilter, $collection): stdClass
    {
        $solrResponse = $this->solrService->paginatedQueryByFacet($pageParams, $sortParams, $facetsFilter, $collection);
        $formatSolrResponse = $this->formatSolrResponseWithWorkspaceInformation($solrResponse);
        $response = $this->handleFacetCards($formatSolrResponse, $collection);
        return $response;
    }

    public function indexByWorkspace($pageParams, $sortParams, $facetsFilter, $workspace): stdClass
    {
        $collection = false;
        if (isset($facetsFilter['collections'])) {
            $collection = Collection::find($facetsFilter['collections']);
        }
        $solrResponse = $this->solrService->distributedPaginatedQueryByFacet($pageParams, $sortParams, $facetsFilter, $workspace);
        $formatSolrResponse = $this->formatSolrResponseWithWorkspaceInformation($solrResponse);
        $response = $this->handleFacetCards($formatSolrResponse, $collection);
        return $response;
    }

    private function handleFacetCards($data, $collection = false)
    {
        if (!$collection instanceof Collection) return $data;

        $type = ucfirst($this->solrConfig->getNameCoreConfig($collection->solr_connection));

        if (class_exists("App\\Services\\{$type}Service")) {
            $resource_service = app("App\\Services\\{$type}Service");
            $data->facets = $resource_service::handleFacetCard($data->facets);
        }
        return $data;
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
                    $newValues['canDelete'] = $this->getWorkspaceCanBeEdit($defaultWorkspace, $workspaceID);
                    $newValues['route_delete'] = route('v1wsp.delete',['workspace_id' => $workspaceID]);
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
