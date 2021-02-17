<?php

namespace App\Services;

use App\Enums\WorkspaceType;
use App\Models\DamResource;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

class UserService
{

    public function user()
    {
        return Auth::user();
    }

    public function resources()
    {

        return Auth::user()->resources();
    }

    public function getWorkspaces()
    {
        return Auth::user()->workspaces()->get();
    }

    public function getOrganizations()
    {
        return Auth::user()->organizations()->get();
    }

    public function getWorkspacesOfOrganization($organization_id)
    {
        return Auth::user()->workspaces()->where('organization_id', $organization_id)->get();
    }

    public function selectOrganization($oid)
    {
        $user = Auth::user();
        $org = Organization::find($oid);

        $user->selected_organization = $org ? $org->id : null;
        $user->save();
        $this->selectWorkspace(null);

        return ["selected organization" => $org];
    }

    public function selectWorkspace($wid)
    {
        $user = Auth::user();
        $wsp = Workspace::find($wid);
        $org = null;
        if($wsp) {
            if($wsp->type == WorkspaceType::personal) {
                $org = $this->selectOrganization(null);
            } else {
                $org = $this->selectOrganization($wsp->organization()->first()->id);
            }

            $user->selected_workspace = $wid;
            $user->save();
        } else {
            $user->selected_workspace = $wid;
            $user->save();
        }
        return [$org, ['selected workspace' => $wsp]];

    }
}
