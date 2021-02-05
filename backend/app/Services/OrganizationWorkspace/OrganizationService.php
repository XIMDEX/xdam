<?php

namespace App\Services\OrganizationWorkspace;

use App\Enums\WorkspaceType;
use App\Http\Requests\OrganizationWorkspace\CreateOrganizationRequest;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Http\Request;

class OrganizationService
{

    public function index()
    {
        $orgs = Organization::all();
        return $orgs;
    }

    public function get(Request $request)
    {
        $org = Organization::find($request->id);
        $org->workspaces = $org->workspaces()->get();
        return $org;
    }

    public function create(CreateOrganizationRequest $request)
    {
        try {
            $org = Organization::create(['name' => $request->name]);
            $wsp = Workspace::create(['name' => $request->name, 'type' => WorkspaceType::corporation]);
            $org->save();
            $org->workspaces()->save($wsp);
            return $org;
        } catch (\Throwable $th) {
            return [$th];
        }
    }

    public function delete($id)
    {
        $org = Organization::find($id);
        if($org != null) {
            $org->delete();
            return ['deleted' => $org];
        } else {
            return ['Organization not exists'];
        }
    }

}
