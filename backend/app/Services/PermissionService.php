<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;


class PermissionService
{

    /**
     * @param Request $request
     * @return Permission
     */
    public function store($request): Permission
    {
        $permission = Permission::create(["name" => $request->name]);
        return $permission;
    }

    public function index(): Collection
    {
        $permissions = Permission::all();
        return $permissions;
    }

    /**
     * @param Permission $permission
     * @throws \Exception
     */
    public function getByName($name): Permission
    {
        $permission = Permission::findByName($name);
        return $permission;
    }

    /**
     * @param Permission $permission
     * @throws \Exception
     */
    public function getById($id): Permission
    {
        $permission = Permission::find($id);
        return $permission;
    }

    /**
     * @param Permission $permission
     * @throws \Exception
     */
    public function update($id): Permission
    {
        $permission = Permission::findOrFail($id);
        //update code
        return $permission;
    }

    /**
     * @param Permission $permission
     * @throws \Exception
     */
    public function delete($id)
    {
        $permission = Permission::findOrFail($id);
        $roles = Role::all();
        foreach ($roles as  $role) {
            $role->revokePermissionTo($permission);
        }
        $permission->delete();
        return $permission;
    }

}
