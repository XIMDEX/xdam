<?php

namespace Lib\Xrole\Services;

use Lib\Xrole\Contracts\PermissionConstants;
use Lib\Xrole\Contracts\PermissionServiceInterface;
use Lib\Xrole\Models\Permissions;

class PermissionService implements PermissionServiceInterface
{
    private $permissions;

    /**
     * PermissionService constructor.
     *
     * @param Permissions $permissions The permissions object.
     */
    public function __construct(Permissions $permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * Checks if the current user has a given permission.
     *
     * @param int $permission The permission to check.
     * @return bool True if the user has the permission, false otherwise.
     */
    public function hasPermission($permission)
    {
        return $this->permissions->hasPermission($permission);
    }

    /**
     * Checks if the current user can search.
     *
     * @return bool True if the user can search, false otherwise.
     */
    public function canSearch()
    {
        return $this->hasPermission(PermissionConstants::SEARCH);
    }

    /**
     * Checks if the current user can read.
     *
     * @return bool True if the user can read, false otherwise.
     */
    public function canRead()
    {
        return $this->hasPermission(PermissionConstants::READ);
    }

    /**
     * Checks if the current user can create.
     *
     * @return bool True if the user can create, false otherwise.
     */
    public function canCreate()
    {
        return $this->hasPermission(PermissionConstants::CREATE);
    }

    /**
     * Checks if the current user can update.
     *
     * @return bool True if the user can update, false otherwise.
     */
    public function canUpdate()
    {
        return $this->hasPermission(PermissionConstants::UPDATE);
    }

    /**
     * Checks if the current user can remove.
     *
     * @return bool True if the user can remove, false otherwise.
     */
    public function canRemove()
    {
        return $this->hasPermission(PermissionConstants::REMOVE);
    }

    /**
     * Checks if the current user can operate.
     *
     * @return bool True if the user can operate, false otherwise.
     */
    public function canOperate()
    {
        return $this->hasPermission(PermissionConstants::OPERATE);
    }

    /**
     * Checks if the current user is an admin.
     *
     * @return bool True if the user is an admin, false otherwise.
     */
    public function isAdmin()
    {
        return $this->hasPermission(PermissionConstants::ADMIN);
    }

    /**
     * Checks if the current user is a super admin.
     *
     * @return bool True if the user is a super admin, false otherwise.
     */
    public function isSuperAdmin()
    {
        return $this->hasPermission(PermissionConstants::SUPERADMIN);
    }
}
