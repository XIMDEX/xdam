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
        foreach ($solrResponse->facets as $key => $value) {
            if ($value['key'] === 'workspaces') {
                $newValues = array();

                foreach ($value['values'] as $subkey => $subvalue) {
                    $newValues[$this->getWorkspaceJSON($subkey)] = $subvalue;
                }

                $solrResponse->facets[$key]['values'] = $newValues;
            }
        }

        return $solrResponse;
    }

    private function getWorkspaceJSON($workspaceName)
    {
        $workspace = Workspace::where('name', $workspaceName)->first();
        
        if ($workspace === null)
            return '';

        $json = ['id' => $workspace->id, 'name' => $workspaceName];
        return json_encode($json);
    }
}
