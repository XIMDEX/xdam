<?php 

namespace App\Services\Amazon;

use App\Services\Solr\SolrService;

class AssignWorkspaceService
{

    private $solrService;
    public function __construct(SolrService $solrService)
    {
        $this->solrService = $solrService;
    }

    public function assignWorkspace($workspaceId, $isbns)
    {   
        foreach ($isbns as $isbn) {
            if(!$this->checkIfResourceHasWorkspace($workspaceId,$isbn)){
                $isbn->workspaces()->attach($workspaceId);
                $isbn->save();
                $isbn->refresh();
                $this->solrService->saveOrUpdateDocument($isbn);
            }
        }
        return true;
    }

    public function unassignWorkspace($workspaceId,$isbns) 
    {
        foreach ($isbns as $isbn) {
            if($this->checkIfResourceHasWorkspace($workspaceId,$isbn)){
                $isbn->workspaces()->detach($workspaceId);
                $isbn->save();
                $isbn->refresh();
                $this->solrService->saveOrUpdateDocument($isbn);
            }
        }
     
        return true;
    }
    
    public function checkIfResourceHasWorkspace($workspaceId, $damResource)
    {
        return $damResource->workspaces()->where('workspace_id', $workspaceId)->exists();
    }

    public function assignIsbn($damResource, $isbn) 
    {
        $damResource->data->categories[] = $isbn;
        $damResource->save();
    }

    public function deassignIsbn($damResource, $isbn) 
    {
        $damResource->data->categories = array_diff($damResource->data->categories, [$isbn]);
        $damResource->save();
    }
}