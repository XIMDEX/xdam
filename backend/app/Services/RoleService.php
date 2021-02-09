<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Silber\Bouncer\Database\Ability;
use Silber\Bouncer\Database\Role;

class RoleService
{

    public function store($name, $title): Role
    {
        $role = Bouncer::role()->firstOrCreate([
            'name' => $name,
            'title' => $title,
        ]);
        return $role;
    }

    public function index(): Collection
    {
        $roles = Role::all();
        return $roles;
    }

    /**
     * @param Role $role
     * @throws \Exception
     */
    public function get($id): Role
    {
        $role = Role::find($id);
        return $role;
    }

    /**
     * @param Role $role
     * @throws \Exception
     */
    public function update($id): Role
    {
        $role = Role::findOrFail($id);
        //update code
        return $role;
    }

    /**
     * @param Role $role
     * @throws \Exception
     */
    public function delete($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return $role;
    }

    public function giveAbility($role_id, $ability = null, $ability_title = null, $ability_id = null)
    {
        $role = Role::find($role_id);
        if($ability_id) {
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
