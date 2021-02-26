<?php

namespace App\Services;

use App\Enums\Roles;
use App\Models\Organization;
use App\Models\Role;
use Exception;
use Illuminate\Support\Facades\Auth;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Silber\Bouncer\Database\Ability;

class RoleService
{

    public function store($organization, $name, $title): Role
    {
        $role = Role::firstOrCreate([
            'name' => $name,
            'title' => $title,
            'organization_id' => $organization->id
        ]);
        return $role;
    }

    public function index(Organization $organization)
    {
        $user = Auth::user();
        $user_organization_roles = Role::where('organization_id', $organization->id)->get();
        return $user->isA(Roles::super_admin) ? [Role::all()] : [$user_organization_roles];
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
    public function update(Organization $organization, $id, array $data): Role
    {
        $role = Role::findOrFail($id)->update($data);
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

    public function giveAbility($role_id, $ability = null, $ability_title = null, $ability_id = null)
    {
        $role = Role::find($role_id);
        if ($ability_id) {
            $ability = Ability::find($ability_id);
        } else {
            $ability = Bouncer::ability()->firstOrCreate([
                'name' => $ability,
                'title' => $ability_title,
            ]);
        }
        $res = Bouncer::allow($role)->to($ability);
        return [$res];
    }

    public function removeAbility($role_id, $ability_id)
    {
        $role = Role::find($role_id);
        $ability = Ability::find($ability_id);
        $res = Bouncer::disallow($role)->to($ability);
        return [$res];
    }
}
