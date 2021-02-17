<?php

namespace App\Services\OrganizationWorkspace;

use App\Enums\Abilities;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Silber\Bouncer\BouncerFacade as Bouncer;

class WorkspaceService
{
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
            if($org) {
                $wsp = Workspace::create(['name' => $wsp_name, 'type' => WorkspaceType::generic]);
                $org->workspaces()->save($wsp);
                Bouncer::allow(Auth::user())->to(Abilities::canManageWorkspace, $wsp);
                return $wsp;
            }
        } catch (\Throwable $th) {
            return [$th];
        }
    }

    public function delete($id)
    {
        $wsp = Workspace::find($id);
        if($wsp != null && $wsp->type != WorkspaceType::public) {
            $wsp->delete();
            return ['deleted' => $wsp];
        } else {
            return ['Workspace not exists or cannot be deleted', $wsp];
        }
    }

    public function update($id, $name)
    {
        $wsp = Workspace::find($id);
        if($wsp != null) {
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
