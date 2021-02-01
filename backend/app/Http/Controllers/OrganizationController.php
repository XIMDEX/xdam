<?php

namespace App\Http\Controllers;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\WorkspaceType;
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

    public function create(Request $request)
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
