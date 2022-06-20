<?php

namespace App\Services\OrganizationWorkspace;

use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Exceptions\Workspace\TooManyWorkspaces;
use App\Exceptions\Workspace\WorkspaceAlreadyExists;
use App\Models\Organization;
use App\Models\Workspace;
use App\Services\Admin\AdminService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class WorkspaceService
{

    private $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
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

    private function createAndAsssignWorkspace(int $organizationId, WorkspaceType $type, string $workspaceName): Workspace
    {
        $workspace = Workspace::create([
            'name' => $workspaceName,
            'type' => WorkspaceType::getValue($type),
            'organization_id' => $organizationId
        ]);

        $this->adminService->setWorkspaces(Auth::user()->id, $workspace->id, (new Roles)->WORKSPACE_MANAGER_ID());
        return $workspace;
    }

    public function create(int $oid, string $wsp_name): Workspace
    {
        try {
            $org = Organization::find($oid);
            if ($org) {

                $workspace = $this->findUniqueWorkspace($oid, WorkspaceType::fromKey('generic') , $wsp_name);

                if (!is_null($workspace)) {
                    throw new WorkspaceAlreadyExists($wsp_name, $oid, WorkspaceType::fromKey('generic'));
                }

                return $this->createAndAsssignWorkspace($oid, WorkspaceType::fromKey('generic'), $wsp_name);
            }
        } catch (\Throwable $th) {
            return [$th];
        }
    }

    public function getOrCreateWorkspace(int $organizationId, string $workspaceName, WorkspaceType $type): Workspace
    {
        $workspace = $this->findUniqueWorkspace($organizationId, $type, $workspaceName);

        if(!is_null($workspace)) {
            return $workspace;
        }

        return $this->createAndAsssignWorkspace($organizationId, $type, $workspaceName);
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
}
