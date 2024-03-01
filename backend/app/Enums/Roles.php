<?php

namespace App\Enums;

use App\Models\Role;
use BenSampo\Enum\Enum;

class Roles
{
    const SUPER_ADMIN = 'super-admin';

    const ORGANIZATION_ADMIN = 'organization-admin';
    const ORGANIZATION_MANAGER = 'organization-manager';
    const ORGANIZATION_USER = 'organization-user';

    const WORKSPACE_MANAGER = 'workspace-manager';
    const WORKSPACE_EDITOR = 'workspace-editor';
    const WORKSPACE_READER = 'workspace-reader';

    //Default organization owner & corporate roles
    const CORPORATE_WORKSPACE_MANAGEMENT = 'corporate-workspace-management';
    const RESOURCE_OWNER = 'resource-owner';

    public $system_default_roles;

    public function __construct()
    {
        try {
            $this->system_default_roles = Role::where(['organization_id' => null, 'system_default' => 1])->get();
        } catch (\Exception $exc) {
            $this->system_default_roles = [];
        }
    }

    public function getId($role_name) {
        foreach ($this->system_default_roles as $role) {
            if($role->name == $role_name) {
                $id = $role->id;
                return $id;
            }
        }
    }

    /**
     * Return ID of the super-admin role in db
     */
    public function SUPER_ADMIN_ID()
    {
        return $this->getId(self::SUPER_ADMIN);
    }

    public function ORGANIZATION_ADMIN_ID()
    {
        return $this->getId(self::ORGANIZATION_ADMIN);
    }

    public function ORGANIZATION_MANAGER_ID()
    {
        return $this->getId(self::ORGANIZATION_MANAGER);
    }

    public function ORGANIZATION_USER_ID()
    {
        return $this->getId(self::ORGANIZATION_USER);
    }

    public function WORKSPACE_MANAGER_ID()
    {
        return $this->getId(self::WORKSPACE_MANAGER);
    }

    public function WORKSPACE_EDITOR_ID()
    {
        return $this->getId(self::WORKSPACE_EDITOR);
    }

    public function WORKSPACE_READER_ID()
    {
        return $this->getId(self::WORKSPACE_READER);
    }

    public function CORPORATE_WORKSPACE_MANAGEMENT_ID($oid)
    {
        return $this->getId(self::CORPORATE_WORKSPACE_MANAGEMENT);
    }

    public function RESOURCE_OWNER_ID($oid)
    {
        return $this->getId(self::RESOURCE_OWNER);
    }
}
