<?php

namespace App\Services\OrganizationWorkspace;

use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\DamResource;
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
            $item->updateWorkspace($current, $default);
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
}
