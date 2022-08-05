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

    /**
     * @param int $organization
     * @return Workspace[]
     */
    public function index($organization)
    {
        //list workspaces of $request->org
        return $organization->workspaces()->get();
    }

    /**
     * @param int $id
     * @return Workspace
     */
    public function get($id)
    {
        $wsp = Workspace::find($id);
        return $wsp;
    }

    /**
     * @param int $oid
     * @param string $wsp_name
     * @return Workspace
     * @throws Throwable
     */
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

    /**
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        $wsp = Workspace::find($id);

        if (!$this->setDefaultWorkspaceToResources($id))
            return ['Workspace not exists or cannot be deleted', $wsp];
        
        if ($wsp != null && !$wsp->isPublic()) {
            $wsp->delete();
            return ['deleted' => $wsp];
        } else {
            return ['Workspace not exists or cannot be deleted', $wsp];
        }
    }

    /**
     * @param int $id
     * @param string $name
     * @return array
     */
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

    /**
     * @param int $wid
     * @return Workspace[]
     */
    public function getResources($wid)
    {
        $wsp = Workspace::find($wid);
        return $wsp->resources()->get();
    }

    /**
     * @param int $oid
     * @return Collection
     */
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

    /**
     * @param int $wid
     * @return boolean
     */
    private function setDefaultWorkspaceToResources($wid)
    {
        $current = $this->get($wid);
        $default = $this->getDefaultWorkspace();
        $resources = $this->getResources($wid);

        if ($default === null || $current === null) return false;
        if ($default->id === $wid) return false;

        foreach ($resources as $item) {
            if (!$this->isWorkspaceAlreadyAssignedToResource($item, $default)) {
                $item->updateWorkspace($current, $default);
            } else {
                $this->removeWorkspaceFromResource($item, $current);
            }

            $this->solrService->saveOrUpdateDocument($item);
        }

        return true;
    }

    /**
     * @return Workspace
     */
    private function getDefaultWorkspace()
    {
        $default = Workspace::where('name', 'Public Workspace')
                    ->where('type', WorkspaceType::public)
                    ->first();

        return $default;
    }

    /**
     * @param int $organizationId
     * @param WorkspaceType $type
     * @param string $workspaceName
     * @return Workspace
     * @throws TooManyWorkspaces
     */
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

    /**
     * @param User $user
     * @param int $resourceID
     * @param int $workspaceID
     * @param string $workspaceName
     * @return array
     */
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

    /**
     * @param DamResource $resource
     * @param Workspace $workspace
     * @return boolean
     */
    private function isWorkspaceAlreadyAssignedToResource(DamResource $resource, Workspace $workspace)
    {
        $resourceWorkspace = DamResourceWorkspace::where('workspace_id', $workspace->id)
                                ->where('dam_resource_id', $resource->id)
                                ->first();
        return $resourceWorkspace !== null;
    }

    /**
     * @param DamResource $resource
     * @param Workspace $workspace
     * @return DamResourceWorkspace
     */
    private function removeWorkspaceFromResource(DamResource $resource, Workspace $workspace)
    {
        $resourceWorkspace = DamResourceWorkspace::where('workspace_id', $workspace->id)
                                ->where('dam_resource_id', $resource->id)
                                ->first()
                                ->delete();
        return $resourceWorkspace;
    }

    /**
     * @param DamResource $resource
     * @param Workspace $workspace
     * @return boolean
     */
    private function updateResourceWorkspace(DamResource $resource, Workspace $workspace) {
        $result = false;

        if ($this->isWorkspaceAlreadyAssignedToResource($resource, $workspace)) {
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
}
