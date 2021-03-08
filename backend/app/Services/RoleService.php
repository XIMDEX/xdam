<?php

namespace App\Services;

use App\Enums\Entities;
use App\Enums\Roles;
use App\Models\Organization;
use App\Models\Role as MyRole;
use App\Models\Workspace;
use Silber\Bouncer\Database\Role;
use Exception;
use Illuminate\Support\Facades\Auth;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Silber\Bouncer\Database\Ability;

class RoleService
{

    public function store($organization, $name, $title, $entity): Role
    {
        $role = MyRole::firstOrCreate([
            'name' => $name,
            'title' => $title,
            'organization_id' => $organization->id,
            'applicable_to_entity' => $entity == Entities::workspace ? Workspace::class : Organization::class,
        ]);
        return $role;
    }

    public function index(Organization $organization)
    {
        $user_organization_roles = Role::where('organization_id', $organization->id)->get();
        $default_roles =  Role::where( ['organization_id' => null, [ 'name', '!=', Roles::SUPER_ADMIN ] ] )->get();
        return [
            'default_roles' => $default_roles,
            'custom_organization_roles' => $user_organization_roles
        ];
    }

    /**
     * @param Role $role
     * @throws \Exception
     */
    public function get(Organization $organization, $role_id)
    {
        if($role = $organization->roles()->where('id', $role_id)->first()) {
            return $role;
        } else {
            throw new Exception('No role found in the organization');
        }
    }

    /**
     * @param Role $role
     * @throws \Exception
     */
    public function update($role_id, $name)
    {
        $role = Role::findOrFail($role_id)->update(
            [
                'name' => $name
            ]
        );
        return $role;
    }

    /**
     * @param Role $role
     * @throws \Exception
     */
    public function delete(Organization $organization, $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return $role;
    }

    public function setAbilityToRole($role_id, array $ability_ids, $action)
    {
        $role = Role::find($role_id);
        $abilities = Ability::find($ability_ids);

        foreach ($abilities as $ability) {
            if ($ability->entity_type != $role->applicable_to_entity) {
                throw new Exception('The ability id: '.$ability->id .' ('. $ability->name . ') of type ' . $ability->entity_type . ' cannot be applied on role '.$role->name.' of type '.$role->applicable_to_entity);
            }
        }

        foreach ($abilities as $ability) {
            $action == 'set' ? Bouncer::allow($role)->to($ability) : Bouncer::disallow($role)->to($ability);
        }

        return $abilities ? ['role' => $role, 'abilities' => $abilities] : ['error' => 'abilities not found'];
    }

}
