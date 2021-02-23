<?php

namespace App\Services\OrganizationWorkspace;

use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\Workspace;
use App\Services\Admin\AdminService;
use Illuminate\Support\Facades\Auth;

class WorkspaceService
{

    private $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function index()
    {
        $wsps = Workspace::all();
        return $wsps;
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
                $this->adminService->setWorkspaces(Auth::user()->id, $wsp->id, Roles::manager);
                return $wsp;
            }
        } catch (\Throwable $th) {
            return [$th];
        }
    }

    public function delete($id)
    {
        $wsp = Workspace::find($id);
        if ($wsp != null && $wsp->type != WorkspaceType::public) {
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
}
