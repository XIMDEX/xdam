<?php

namespace App\Services\OrganizationWorkspace;

use App\Enums\WorkspaceType;
use App\Models\Collection;
use App\Models\CollectionType;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

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

    public function createCollection($oid, $name, $type_id)
    {
        try {
            $org = Organization::find($oid);
            $collType = CollectionType::find($type_id);
            if($org && $collType) {

                $collection = Collection::create([
                    "name" => $name
                ]);

                $collection->type_id = $collType->id;
                $collection->organization_id = $org->id;
                $collection->save();
                return $collection;
            }
            return false;
        } catch (\Throwable $th) {
            return [$th];
        }
    }

    public function indexCollections()
    {
        $selected_org = Auth::user()->selected_organization;
        if($selected_org) {
            $org = Organization::find($selected_org);
            return $org->collections()->get();
        }
        return ['warning'=>'organization not selected'];
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
