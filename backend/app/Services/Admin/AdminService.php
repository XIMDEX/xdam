<?php

namespace App\Services\Admin;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\Entities;
use App\Enums\OrganizationType;
use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use Silber\Bouncer\Database\Role;
use App\Models\User;
use App\Models\Workspace;
use Exception;
use Silber\Bouncer\BouncerFacade as Bouncer;

class AdminService
{

    public $rolesService;

    public function __construct(Roles $roles)
    {
        $this->rolesService = $roles;
    }

    public function setOrganizations(string $user_id, string $organization_id, string $role_id)
    {
        $log = [];
        $user = User::find($user_id);
        if (!$user->organizations()->get()->contains($organization_id)) {
            if (!$org = Organization::find($organization_id)) {
                throw new Exception("organization with id " . $organization_id . " doesn't exist");
            }

            $this->SetRoleAbilitiesOnEntity($user_id, $role_id, $organization_id, 'set', Entities::organization);

            if ($role_id == $this->rolesService->ORGANIZATION_ADMIN_ID() || $role_id == $this->rolesService->ORGANIZATION_MANAGER_ID()) {
                foreach ($org->workspaces()->get() as $wsp) {
                    $this->setWorkspaces($user_id, $wsp->id, $this->rolesService->WORKSPACE_MANAGER_ID());
                }
            } else {
                $this->setDefaultWorkspace($org, $user);
            }

            $user->organizations()->attach($organization_id);

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
                $this->SetRoleAbilitiesOnEntity($user_id, $role_id, $wsp->id, 'set', Entities::workspace);
                $log['success']['workspace_id'] = $wsp->id;
            } else {
                $log['already_attached']['workspace_id'][] = $wsp->id;
            }
        }

        return ['user' => $user, 'log' => $log];
    }

    public function unsetOrganizations(string $user_id, string $organization_id)
    {
        $user = User::find($user_id);
        $log = [];
        $user_organizations = $user->organizations()->get();
        $org = Organization::find($organization_id);

        if ($user_organizations->contains($org)) {
            $user->organizations()->detach($org);
            $log['detach_success']['organization_id'] = $organization_id;
            $log['removed_abilities']['on_org'] = $this->removeAllAbilities($user, $org);
            foreach ($org->workspaces()->get() as $wsp) {
                if ($wsp->name == DefaultOrganizationWorkspace::public_workspace || $wsp->isPublic()) {
                    continue;
                }

                if ($user->workspaces()->get()->contains($wsp->id)) {
                    $user->workspaces()->detach($wsp->id);
                    $log['detach_success']['workspaces_ids'][] = $wsp->id;
                    $log['removed_abilities']['on_wsp'] = $this->removeAllAbilities($user, $wsp);

                }
            }

        } else {
            $log['error'][] = 'organization id ' . $organization_id. ' not exists, or user isn´t attached to it';
        }

        return ['user' => $user, 'log' => $log];
    }

    public function setWorkspaces(string $user_id, string $workspace_id, string $with_role_id)
    {
        $user = User::find($user_id);
        $log = [];

        if (!$user->workspaces()->get()->contains($workspace_id)) {
            $user->workspaces()->attach($workspace_id);
            $this->SetRoleAbilitiesOnEntity($user_id, $with_role_id, $workspace_id, 'set', Entities::workspace);
            $log['success']['workspace_id'] = $workspace_id;
        } else {
            $log['already_exists']['workspace_id'][] = $workspace_id;
        }

        return ['user' => $user, 'log' => $log];
    }

    public function unsetWorkspaces($user_id, string $workspace_id)
    {
        $user = User::find($user_id);
        $wsp = Workspace::find($workspace_id);
        $log = [];

        if ($user->workspaces()->get()->contains($wsp)) {
            if ($wsp->isPublic()) {
                throw new Exception('cannot unset public workspace');
            }
            $this->removeAllAbilities($user, $wsp);
            $user->workspaces()->detach($wsp);
            $log['success']['workspace_id'] = $workspace_id;
        } else {
            $log['relation_not_exists']['workspace_id'] = $workspace_id;
        }
        return ['user' => $user, 'log' => $log];
    }

    public function setDefaultWorkspace($org, $user)
    {
        $wsp_type = $org->type == OrganizationType::public ? WorkspaceType::public : WorkspaceType::corporate;
        $wsp = $org->workspaces()->where('type', $wsp_type)->first();
        //if wsp->type == corporate, apply the "corporate" role of the organization. Here get the public role of the organization.
        //For now, only can read workspaces.
        // $wsp->type == WorkspaceType::corporate ? $this->setWorkspaces($user->id, $wsp->id, $role_id) :
        // $this->setWorkspaces($user->id, $wsp->id, $this->rolesService->WORKSPACE_READER_ID());
        $this->setWorkspaces($user->id, $wsp->id, $this->rolesService->WORKSPACE_READER_ID());
    }

    public function getRoleAbilities($rid, $entity)
    {
        $role = Role::find($rid);

        if ($entity instanceof Workspace && $role->applicable_to_entity != Workspace::class) {
            throw new Exception('This role is only applicable on entity Organization');
        }
        if ($entity instanceof Organization && $role->applicable_to_entity != Organization::class) {
            throw new Exception('This role is only applicable on entity Workspace');
        }

        $abilities = [];
        foreach ($role->getAbilities()->toArray() as $ability) {
            $abilities[] = $ability['name'];
        }
        return $abilities;
    }

    /**
     * @param int $uid (user id)
     * @param int $rid (role id)
     * @param int $eid (organization or workspace)
     * @param string $type (type of action: 'set' or 'unset')
     * @param string $on (what entity 'org' or 'wsp')
     */
    public function SetRoleAbilitiesOnEntity(
        int $uid,
        int $rid,
        int $eid,
        string $type,
        string $on
    ) {
        $user = User::find($uid);
        $entity = null;
        $entity = $on == Entities::workspace ? Workspace::find($eid) : Organization::find($eid);

        $abilities = $this->getRoleAbilities($rid, $entity);

        $type == 'set' ? Bouncer::allow($user)->to($abilities, $entity) : Bouncer::disallow($user)->to($abilities, $entity);
        return ['user' => $user, $type.'_abilities' => $abilities, 'on_'.$on => $entity];
    }

    /**
     * Remove all user abilities of an entity
     */
    public function removeAllAbilities(User $user, $entity): array
    {
        $log = [];
        $user_abilities = $user->getAbilities();
        foreach ($user_abilities as $ability) {
            if ($ability->entity_type == get_class($entity)) {
                Bouncer::disallow($user)->to($ability->name, $entity);
                $log['removed_abilities'][] = $ability;
            }
        }
        return $log;
    }
}
