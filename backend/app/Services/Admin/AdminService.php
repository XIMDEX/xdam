<?php

namespace App\Services\Admin;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\OrganizationType;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use Error;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Silber\Bouncer\Database\Role;

class AdminService
{

    public function setOrganizations(string $user_id, string $organization_id, string $role_id)
    {
        $log = [];

        $user = User::find($user_id);

        if (!$user->organizations()->get()->contains($organization_id))
        {
            $org = Organization::find($organization_id);

            if (!$org)
            {
                $log['not_exists'][] = "organization with id " . $organization_id . " doesn't exists";
                return;
            }

            $user->organizations()->attach($organization_id);

            $this->roleAbilitiesOnWorkspaceOrOrganization($user_id, $role_id, $organization_id, 'set', 'org');

            $this->enableDefaultWorkspace($org, $user, $role_id);

            $log['success'][] = [
                "user_id" => $user_id,
                "attached to" => $organization_id,
                "with_role" => $role_id
            ];
        } else {
            $log['already_exists'][] = "user already attached to organization " . $organization_id;
        }

        return $log;
    }

    public function setAllWorkspacesOfOrganization(string $user_id, string $organization_id, string $role_id)
    {
        $user = User::find($user_id);
        $log = [];
        $org = Organization::find($organization_id);

        foreach ($org->workspaces()->get() as $wsp) {
            if (!$user->workspaces()->get()->contains($wsp->id)) {
                $user->workspaces()->attach($wsp->id);
                $this->roleAbilitiesOnWorkspaceOrOrganization($user_id, $role_id, $wsp->id, 'set', 'wsp');
                $log['success']['workspace_id'] = $wsp->id;
            } else {
                $log['already_exists']['workspace_id'][] = $wsp->id;
            }
        }

        return ['user' => $user, 'log' => $log];
    }

    public function unsetOrganizations(string $user_id, string $organization_id)
    {
        $user = User::find($user_id);
        $log = [];

        if ($user->organizations()->get()->contains($organization_id)) {
            $user->organizations()->detach($organization_id);

            foreach (Organization::find($organization_id)->workspaces()->get() as $wsp) {
                if ($wsp->name == DefaultOrganizationWorkspace::public_workspace) {
                    continue;
                }

                if ($user->workspaces()->get()->contains($wsp->id)) {
                    $user->workspaces()->detach($wsp->id);
                    $log['detach success']['workspaces_ids'][] = $wsp->id;
                }
            }
            $log['detach success']['organization_id'] = $organization_id;
        } else {
            $log['not_exists']['organization_id'] = $organization_id;
        }

        return ['user' => $user, 'log' => $log];
    }

    public function setWorkspaces(string $user_id, string $workspace_id, string $with_role_id)
    {
        $user = User::find($user_id);
        $log = [];

        if (!$user->workspaces()->get()->contains($workspace_id)) {
            $user->workspaces()->attach($workspace_id);
            $this->roleAbilitiesOnWorkspaceOrOrganization($user_id, $with_role_id, $workspace_id, 'set', 'wsp');
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

        if ($user->workspaces()->where('workspaces.id', $workspace_id)->first()->type == WorkspaceType::public ? true : false) {
            $log['error'][] = 'cannot unset public workspace';
            return $log;
        }

        if ($user->workspaces()->get()->contains($workspace_id)) {
            $user->workspaces()->detach($workspace_id);
            $log['success']['workspace_id'] = $workspace_id;
        } else {
            $log['relation_not_exists']['workspace_id'] = $workspace_id;
        }
        return ['user' => $user, 'log' => $log];
    }

    public function enableDefaultWorkspace($org, $user, $role_id)
    {

        switch ($org->type) {
            case OrganizationType::public:
                $wsp_type = WorkspaceType::public ;
                break;

            case OrganizationType::personal:
                $wsp_type = WorkspaceType::personal ;
                break;

            case OrganizationType::corporate:
                $wsp_type = WorkspaceType::corporate ;
                break;

            default:
                throw new Error('Undefined organizationtype');
                break;
        }

        $default_org_wsp_id = $org->workspaces()->where('type', $wsp_type)->first()->id;
        $this->setWorkspaces($user->id, $default_org_wsp_id, $role_id);
    }

    public function getRoleAbilities($rid)
    {
        $role = Role::find($rid);
        $abilities = [];
        foreach ($role->getAbilities()->toArray() as $ability) {
            $abilities[] = $ability['name'];
        }
        return $abilities;
    }

    /**
     * @param string $uid (user id)
     * @param string $rid (role id)
     * @param string $owid (organization or workspace id)
     * @param string $type (type of action: 'set' or 'unset')
     * @param string $on (what entity 'org' or 'wsp')
     */
    public function roleAbilitiesOnWorkspaceOrOrganization(
        string $uid,
        string $rid,
        string $owid,
        string $type,
        string $on
    ) {
        $user = User::find($uid);
        $entity = null;
        $abilities = $this->getRoleAbilities($rid);
        $on == 'wsp' ? $entity = Workspace::find($owid) : $entity = Organization::find($owid);
        $type == 'set' ? Bouncer::allow($user)->to($abilities, $entity) : Bouncer::disallow($user)->to($abilities, $entity);
        return ['user' => $user, $type.'_abilities' => $abilities, 'on_'.$on => $entity];
    }
}