<?php

namespace App\Http\Controllers;

use App\Enums\WorkspaceType;
use App\Http\Requests\OrganizationWorkspace\SetOrganizationRequest;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $orgs = Organization::all();
        return $orgs;
    }

    public function get(Request $request)
    {
        $org = Organization::findOne($request->id);
        return $org;
    }

    public function create(SetOrganizationRequest $request)
    {
        $org = new Organization(['name' => $request->name]);
        $wsp = new Workspace(
            [
                'name' => $request->name,
                'type' => WorkspaceType::corporation
            ]
        );
        $org->save();
        $org->workspaces()->save($wsp);
        return $org;
    }
}
