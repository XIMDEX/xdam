<?php

namespace App\Services\OrganizationWorkspace;

use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\DamResource;
use App\Models\DamResourceWorkspace;
use App\Models\Organization;
use App\Models\Workspace;
use App\Services\Admin\AdminService;
use App\Services\Solr\SolrService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class WorkspaceService
{

    private $adminService;

    private $solrService;

    public function __construct(AdminService $adminService, SolrService $solrService)
    {
        $this->adminService = $adminService;
        $this->solrService = $solrService;
    }

    public function index($organization)
    {
        //list workspaces of $request->org
        return $organization->workspaces()->get();
    }

    public function get($id)
    {
        $wsp = Workspace::find($id);
        return $wsp;
    }

    public function create($oid, $wsp_name)
    {
        try {
            $org = Organization::find($oid);
            if ($org) {
                $wsp = Workspace::create([
                    'name' => $wsp_name,
                    'type' => WorkspaceType::generic,
                    'organization_id' => $org->id
                ]);
                $this->adminService->setWorkspaces(Auth::user()->id, $wsp->id, (new Roles)->WORKSPACE_MANAGER_ID());
                return $wsp;
            }
        } catch (\Throwable $th) {
            return [$th];
        }
    }

    public function delete($id)
    {
        $wsp = Workspace::find($id);
        if ($wsp != null && !$wsp->isPublic()) {
            $wsp->delete();
            return ['deleted' => $wsp];
        } else {
            return ['Workspace not exists or cannot be deleted', $wsp];
        }
    }

    public function update($id, $name)
    {
        $wsp = Workspace::find($id);
        if ($wsp != null) {
            $wsp->update(['name' => $name]);
            return ['updated' => $wsp];
        } else {
            return ['Workspace not exists'];
        }
    }

    public function getResources($wid)
    {
        $wsp = Workspace::find($wid);
        return $wsp->resources()->get();
    }

    public function getOrganizationResources($oid)
    {
        $res = new Collection();
        $collections = Organization::find($oid)->collections()->get();
        foreach ($collections as $coll) {
            foreach ($coll->resources()->get() as $dam) {
                $res->add($dam);
            }
        }
        return $res;
    }

    public function setResourceWorkspace($user, $resourceID, $workspaceID, $workspaceName)
    {
        $resource = DamResource::find($resourceID);
        $collection = $resource->collection()->first();
        $currentOrganization = $collection->organization()->first();
        $workspace = Workspace::where('id', $workspaceID)->first();
        $result = false;

        if ($resource === null)
            return ['error' => 'The specified resource doesn\'t exist.'];

        if (!isset($workspaceID)) {
            $newWorkspace = $this->create($currentOrganization->id, $workspaceName);

            if ($newWorkspace === null)
                return ['error' => 'The workspace couldn\'t be created.'];

            $workspace = $newWorkspace;
        }

        if ($workspace === null)
            return ['error' => 'The specified workspace doesn\'t exist'];

        $result = $this->updateResourceWorkspace($resource, $workspace);
        return ['status' => $result, 'resource' => $resource, 'workspace' => $workspace];
    }

    private function updateResourceWorkspace($resource, $workspace) {
        $resourceWorkspace = DamResourceWorkspace::where('workspace_id', $workspace->id)
                                ->where('dam_resource_id', $resource->id)
                                ->first();
        $result = false;

        if ($resourceWorkspace !== null) {
            $result = true;
        } else {
            $resourceWorkspace = DamResourceWorkspace::create([
                'workspace_id'      => $workspace->id,
                'dam_resource_id'   => $resource->id
            ]);
            $result = ($resourceWorkspace !== null);
        }

        $this->solrService->saveOrUpdateDocument($resource);
        return $result;
    }

    private function findUniqueWorkspace(int $organizationId, WorkspaceType $type, string $workspaceName): ?Workspace
    {
        $workpaceCollection = Workspace::select('*')
            ->where('organization_id', '=', $organizationId)
            ->where('type', '=', $type)
            ->where('name', '=', $workspaceName)
            ->get();

        if($workpaceCollection->count() === 0) {
            return null;
        }

        if($workpaceCollection->count() > 1) {
            throw new TooManyWorkspaces($workspaceName);
        }

        return $workpaceCollection->values()->first();
    }
    
     /**
      * @param int[] $workspacesId
      * @return Worspace[]
      */
    public function getMultpleWorkspaces(array $workspacesId): Collection
    {
        $collection = Workspace::whereIn('id', $workspacesId)->get();

        return $collection;
    }
}
