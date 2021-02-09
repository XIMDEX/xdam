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
                $this->enableDefaultWorkspace($org, $user);
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

    public function unsetWorkspaces($user_id, array $workspaces_id)
    {
        $user = User::find($user_id);
        $log = [];
        $checkForPublic = true;
        foreach ($workspaces_id as $wid) {
            if($checkForPublic) {
                if($user->workspaces()->where('workspaces.id', $wid)->first()->type == WorkspaceType::public ? true : false) {
                    $log['error'][] = 'cannot unset public workspace';
                    $checkForPublic = false;
                    continue;
                }
            }
            if($user->workspaces()->get()->contains($wid)) {
                $user->workspaces()->detach($wid);
                $log['success']['workspace_id'] = $wid;
            } else {
                $log['relation_not_exists']['workspace_id'] = $wid;
            }

        }
        return ['user' => $user, 'log' => $log];
    }

    public function enableDefaultWorkspace($org, $user) {

        if($org->name == DefaultOrganizationWorkspace::public_organization) {
            $default_org_wsp_id = $org->workspaces()->where('type', WorkspaceType::public)->first()->id;
        } else {
            $default_org_wsp_id = $org->workspaces()->where('type', WorkspaceType::corporation)->first()->id;
        }
        $this->setWorkspaces($user->id, [$default_org_wsp_id]);
    }

    public function getRoleAbilities( $rid) {
        $role = Role::find($rid);
        $abilities = [];
        foreach ($role->getAbilities()->toArray() as $ability) {
            $abilities[] = $ability['name'];
        }
        return $abilities;
    }

    public function setRoleAbilitiesOnWorkspace($uid, $rid, $wid) {
        $user = User::find($uid);
        $wsp = Workspace::find($wid);
        $abilities = $this->getRoleAbilities($rid);
        Bouncer::allow($user)->to($abilities, $wsp);
        return ['user' => $user, 'added_abilities' => $abilities, 'on_workspace' => $wsp];
    }

    public function unsetRoleAbilitiesOnWorkspace($uid, $rid, $wid) {
        $user = User::find($uid);
        $wsp = Workspace::find($wid);
        $abilities = $this->getRoleAbilities($rid);
        Bouncer::disallow($user)->to($abilities, $wsp);
        return ['user' => $user, 'removed_abilities' => $abilities, 'on_workspace' => $wsp];
    }
}
