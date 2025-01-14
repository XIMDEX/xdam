<?php

use PHPUnit\Framework\TestCase;
use Ximdex\Xrole\Models\Permissions;
use Ximdex\Xrole\Contracts\PermissionConstants;
use Ximdex\Xrole\Services\PermissionService;

class PermissionServiceTest extends TestCase {
    public function testCanSearch() {
        $permissions = new Permissions(PermissionConstants::SEARCH);
        $permissionService = new PermissionService($permissions);
        $this->assertTrue($permissionService->canSearch());
    }

    public function testCanRead() {
        $permissions = new Permissions(PermissionConstants::READ);
        $permissionService = new PermissionService($permissions);
        $this->assertTrue($permissionService->canRead());
    }

    public function testCanCreate() {
        $permissions = new Permissions(PermissionConstants::CREATE);
        $permissionService = new PermissionService($permissions);
        $this->assertTrue($permissionService->canCreate());
    }

    public function testCanUpdate() {
        $permissions = new Permissions(PermissionConstants::UPDATE);
        $permissionService = new PermissionService($permissions);
        $this->assertTrue($permissionService->canUpdate());
    }

    public function testCanRemove() {
        $permissions = new Permissions(PermissionConstants::REMOVE);
        $permissionService = new PermissionService($permissions);
        $this->assertTrue($permissionService->canRemove());
    }

    public function testCanOperate() {
        $permissions = new Permissions(PermissionConstants::OPERATE);
        $permissionService = new PermissionService($permissions);
        $this->assertTrue($permissionService->canOperate());
    }

    public function testIsAdmin() {
        $permissions = new Permissions(PermissionConstants::ADMIN);
        $permissionService = new PermissionService($permissions);
        $this->assertTrue($permissionService->isAdmin());
    }

    public function testIsSuperAdmin() {
        $permissions = new Permissions(PermissionConstants::SUPERADMIN);
        $permissionService = new PermissionService($permissions);
        $this->assertTrue($permissionService->isSuperAdmin());
    }

    public function testCannotSearchWhenPermissionNotSet() {
        $permissions = new Permissions(0); // No permissions set
        $permissionService = new PermissionService($permissions);
        $this->assertFalse($permissionService->canSearch());
    }

    public function testCannotReadWhenPermissionNotSet() {
        $permissions = new Permissions(0); // No permissions set
        $permissionService = new PermissionService($permissions);
        $this->assertFalse($permissionService->canRead());
    }

    public function testCannotCreateWhenPermissionNotSet() {
        $permissions = new Permissions(0); // No permissions set
        $permissionService = new PermissionService($permissions);
        $this->assertFalse($permissionService->canCreate());
    }

    public function testCannotUpdateWhenPermissionNotSet() {
        $permissions = new Permissions(0); // No permissions set
        $permissionService = new PermissionService($permissions);
        $this->assertFalse($permissionService->canUpdate());
    }

    public function testCannotRemoveWhenPermissionNotSet() {
        $permissions = new Permissions(0); // No permissions set
        $permissionService = new PermissionService($permissions);
        $this->assertFalse($permissionService->canRemove());
    }

    public function testCannotOperateWhenPermissionNotSet() {
        $permissions = new Permissions(0); // No permissions set
        $permissionService = new PermissionService($permissions);
        $this->assertFalse($permissionService->canOperate());
    }

    public function testIsNotAdminWhenPermissionNotSet() {
        $permissions = new Permissions(0); // No permissions set
        $permissionService = new PermissionService($permissions);
        $this->assertFalse($permissionService->isAdmin());
    }

    public function testIsNotSuperAdminWhenPermissionNotSet() {
        $permissions = new Permissions(0); // No permissions set
        $permissionService = new PermissionService($permissions);
        $this->assertFalse($permissionService->isSuperAdmin());
    }

    
}