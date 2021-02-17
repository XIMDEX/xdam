<?php

namespace App\Services\Admin;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Silber\Bouncer\Database\Role;

class AdminService
{

    public function setOrganizations(string $user_id, string $organization_id, string $role_id = null)
    {
        $log = [];

        $user = User::find($user_id);

        if(!$user->organizations()->get()->contains($organization_id)) {
            $org = Organization::find($organization_id);
            if(!$org) {
                $log['not_exists'][] = "organization with id " . $organization_id . " doesn't exists";
                return;
            }
            $user->organizations()->attach($organization_id);
            if($role_id)
                $this->roleAbilitiesOnWorkspaceOrOrganization($user_id, $role_id, $organization_id, 'set', 'org');

            $this->enableDefaultWorkspace($org, $user);
            $log['success'][] = ["user_id" => $user_id, "attached to" => $organization_id];
        } else {
            $log['already_exists'][] = "user already attached to organization " . $organization_id;
        }

        return $log;
    }

    public function unsetOrganizations(string $user_id, string $organization_id)
    {
        $user = User::find($user_id);
        $log = [];

        if($user->organizations()->get()->contains($organization_id)) {
            $user->organizations()->detach($organization_id);
            $log['success']['organization_id'] = $organization_id;
        } else {
            $log['not_exists']['organization_id'] = $organization_id;
        }

        return ['user' => $user, 'log' => $log];
    }

    public function setWorkspaces(string $user_id, string $workspace_id)
    {
        $user = User::find($user_id);
        $log = [];

        if(!$user->workspaces()->get()->contains($workspace_id)) {
            $user->workspaces()->attach($workspace_id);
            $log['success']['workspace_id'] = $workspace_id;
        } else {
            $log['already_exists']['workspace_id'][] = $workspace_id;
        }

        return ['user' => $user, 'log' => $log];
    }

    public function unsetWorkspaces($user_id, string $workspace_id)
    {
        $user = User::find($user_id);
        $log = [];
        if($user->workspaces()->where('workspaces.id', $workspace_id)->first()->type == WorkspaceType::public ? true : false) {
            $log['error'][] = 'cannot unset public workspace';
            return $log;
        }
        if($user->workspaces()->get()->contains($workspace_id)) {
            $user->workspaces()->detach($workspace_id);
            $log['success']['workspace_id'] = $workspace_id;
        } else {
            $log['relation_not_exists']['workspace_id'] = $workspace_id;
        }
        return ['user' => $user, 'log' => $log];
    }

    public function enableDefaultWorkspace($org, $user) {

        if($org->name == DefaultOrganizationWorkspace::public_organization) {
            $default_org_wsp_id = $org->workspaces()->where('type', WorkspaceType::public)->first()->id;
        } else {
            $default_org_wsp_id = $org->workspaces()->where('type', WorkspaceType::corporation)->first()->id;
        }
        $this->setWorkspaces($user->id, $default_org_wsp_id);
    }

    public function getRoleAbilities( $rid) {
        $role = Role::find($rid);
        $abilities = [];
        foreach ($role->getAbilities()->toArray() as $ability) {
            $abilities[] = $ability['name'];
        }
        return $abilities;
    }

    public function roleAbilitiesOnWorkspaceOrOrganization($uid, $rid, $owid, $type, $on) {
        $user = User::find($uid);
        $entity = null;
        $abilities = $this->getRoleAbilities($rid);
        if($on == 'wsp') {
            $wsp = Workspace::find($owid);
            $entity = $wsp;
        } else {
            $org = Organization::find($owid);
            $entity = $org;
        }
        if($type == 'set') {
            Bouncer::allow($user)->to($abilities, $entity);
        } else {
            Bouncer::disallow($user)->to($abilities, $entity);
        }
        return ['user' => $user, $type.'_abilities' => $abilities, 'on_'.$on => $entity];

    }
}
