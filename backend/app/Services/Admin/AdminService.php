<?php

namespace App\Services\Admin;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\User;

class AdminService
{

    public function setOrganizations(string $user_id, array $organization_ids)
    {
        $user = User::find($user_id);
        $log = [];
        foreach ($organization_ids as $oid) {
            if(!$user->organizations()->get()->contains($oid)) {
                $org = Organization::find($oid);
                if(!$org) {
                    $log['not_exists'][] = "organization with id " . $oid . " doesn't exists";
                    continue;
                }
                $user->organizations()->attach($oid);
                $this->enableDefaultWorkspace($oid, $user_id);
                $log['success'][] = "attached to " . $oid;
            } else {
                $log['already_exists'][] = "user already attached to organization " . $oid;
            }
        }
        return $log;
    }

    public function unsetOrganizations(string $user_id, array $organization_ids)
    {
        $user = User::find($user_id);
        $log = [];
        foreach ($organization_ids as $oid) {

            if($user->organizations()->get()->contains($oid)) {
                $user->organizations()->detach($oid);
                $this->disableDefaultWorkspace($oid, $user_id);
                $log['success']['organization_id'] = $oid;
            } else {
                $log['not_exists']['organization_id'] = $oid;
            }
        }
        return ['user' => $user, 'log' => $log];
    }

    public function setWorkspaces(string $user_id, array $workspaces_id)
    {
        $user = User::find($user_id);
        $log = [];
        foreach ($workspaces_id as $oid) {
            if(!$user->workspaces()->get()->contains($oid)) {
                $user->workspaces()->attach($oid);

                $log['success']['organization_id'] = $oid;
            } else {
                $log['already_exists']['organization_id'] = $oid;
            }
        }
        return ['user' => $user, 'log' => $log];
    }

    public function unsetWorkspaces(string $user_id, array $workspaces_id)
    {
        $user = User::find($user_id);
        $log = [];
        foreach ($workspaces_id as $oid) {
            if($user->workspaces()->get()->contains($oid)) {
                $user->workspaces()->detach($oid);

                $log['success']['organization_id'] = $oid;
            } else {
                $log['already_exists']['organization_id'] = $oid;
            }
        }
        return ['user' => $user, 'log' => $log];
    }

    public function enableDefaultWorkspace($oid, $uid) {

        $org = Organization::find($oid);

        if($org->name == DefaultOrganizationWorkspace::public_organization) {
            $default_org_wsp_id = $org->workspaces()->where('type', WorkspaceType::public)->first()->id;
        } else {
            $default_org_wsp_id = $org->workspaces()->where('type', WorkspaceType::corporation)->first()->id;
        }

        $this->setWorkspaces($uid, [$default_org_wsp_id]);
    }

    public function disableDefaultWorkspace($oid, $uid) {
        $org = Organization::where('id',$oid)->first();

        if($org->name == DefaultOrganizationWorkspace::public_organization) {
            $default_org_wsp_id = $org->workspaces()->where('type', WorkspaceType::public)->first()->id;
        } else {
            $default_org_wsp_id = $org->workspaces()->where('type', WorkspaceType::corporation)->first()->id;
        }

        $this->unsetWorkspaces($uid, [$default_org_wsp_id]);
    }

}
