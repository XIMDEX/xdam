<?php

namespace App\Services\OrganizationWorkspace;

use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\Workspace;


class OrganizationService
{

    public function index()
    {
        $orgs = Organization::all();
        return $orgs;
    }

    public function get($id)
    {
        $org = Organization::find($id);
        $org->workspaces = $org->workspaces()->get();
        return $org;
    }

    public function create($name)
    {
        try {
            $org = Organization::create(['name' => $name]);
            $wsp = Workspace::create(['name' => $name, 'type' => WorkspaceType::corporation]);
            $org->save();
            $org->workspaces()->save($wsp);
            return $org;
        } catch (\Throwable $th) {
            return [$th];
        }
    }

    public function delete($id)
    {
        if($id == 1)
            return ['Public organization cannot be deleted'];

        $org = Organization::find($id);
        if($org != null) {
            $org->delete();
            return ['deleted' => $org];
        } else {
            return ['Organization not exists'];
        }
    }

    public function update($id, $name)
    {
        $org = Organization::find($id);
        if($org != null) {
            $org->update(['name' => $name]);
            return ['updated' => $org];
        } else {
            return ['Organization not exists'];
        }
    }

}
