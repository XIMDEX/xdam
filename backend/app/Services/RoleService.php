<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Role;

class RoleService
{
    private $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function givePermission($request)
    {
        $role = $this->getById($request->role_id);
        $permission = $this->permissionService->getById($request->permission_id);
        $role->givePermissionTo($permission);
        return $role;
    }

    public function revokePermission($request)
    {
        $role = $this->getById($request->role_id);
        $permission = $this->permissionService->getById($request->permission_id);
        $role->revokePermissionTo($permission);
        return $role;
    }
    /**
     * @param Request $request
     * @return Role
     */
    public function store($request): Role
    {
        $role = Role::create(["name" => $request->name]);
        return $role;
    }

    public function index(): Collection
    {
        $roles = Role::all();
        foreach ($roles as $key=>$role) {
            $roles[$key]->permission = $role->permissions()->get();
        }
        return $roles;
    }

    /**
     * @param Role $role
     * @throws \Exception
     */
    public function getByName($name): Role
    {
        $role = Role::findByName($name);
        $role->permissions = $role->permissions()->get();
        return $role;
    }

    /**
     * @param Role $role
     * @throws \Exception
     */
    public function getById($id): Role
    {

        $role = Role::findByUuId($id);
        $role->permissions = $role->permissions()->get();
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

}
