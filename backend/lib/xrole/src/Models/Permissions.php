<?php

namespace Lib\Xrole\Models;

use Lib\Xrole\Contracts\PermissionsInterface;

class Permissions implements PermissionsInterface
{
    private $permissionCode = 0;

    /**
     * Constructs a new instance of the class with the given permissions.
     *
     * @param int $permissions The permissions to initialize the object with. Defaults to 0.
     */
    public function __construct($permissions = 0)
    {
        if (!is_int($permissions) || $permissions < 0) {
            throw new \InvalidArgumentException("Permissions must be a non-negative integer");
        }
        $this->permissionCode = $permissions;
    }

    /**
     * Checks if the given permission is present in the permission code.
     *
     * @param int $permission The permission to check.
     * @return bool Returns true if the permission is present, false otherwise.
     */
    public function hasPermission($permission)
    {
        return ($this->permissionCode & $permission) === $permission;
    }
}
