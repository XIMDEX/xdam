<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationWorkspace\SetWorkspaceRequest;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function index()
    {
        $orgs = Workspace::all();
        return $orgs;
    }

    public function get(Request $request)
    {
        $org = Workspace::findOne($request->id);
        return $org;
    }

    public function create(SetWorkspaceRequest $request)
    {
        $org = Organization::find($request->organization_id);
        $wsp = new Workspace(['name' => $request->name]);
        $org->workspaces()->save($wsp);
        return $org;
    }
}
