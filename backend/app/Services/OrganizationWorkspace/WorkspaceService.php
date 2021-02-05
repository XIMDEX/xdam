<?php

namespace App\Services\OrganizationWorkspace;

use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\Workspace;

class WorkspaceService
{
    public function index()
    {
        $wsps = Workspace::all();
        return $wsps;
    }

    public function get($id)
    {
        $org = Workspace::find($id);
        return $org;
    }

    public function create($oid, $wsp_name)
    {
        try {
            $org = Organization::find($oid);
            $wsp = Workspace::create(['name' => $wsp_name, 'type' => WorkspaceType::generic]);
            $org->workspaces()->save($wsp);
            return $org;
        } catch (\Throwable $th) {
            return [$th];
        }
    }

    public function delete($id)
    {
        $wsp = Workspace::find($id);
        if($wsp != null) {
            $wsp->delete();
            return ['deleted' => $wsp];
        } else {
            return ['Organization not exists'];
        }
    }
}
