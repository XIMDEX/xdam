<?php

use PHPUnit\Framework\TestCase;
use Ximdex\Xrole\Models\Permissions;
use Ximdex\Xrole\Contracts\PermissionConstants;

class PermissionsTest extends TestCase {
    
    public function testConstructorSetsInitialPermissionCode() {
        $permissions = new Permissions(PermissionConstants::READ);
        $this->assertTrue($permissions->hasPermission(PermissionConstants::READ));
    }

    public function testHasPermissionIdentifiesSetPermissions() {
        $permissions = new Permissions(PermissionConstants::READ | PermissionConstants::SEARCH);
        $this->assertTrue($permissions->hasPermission(PermissionConstants::READ));
        $this->assertTrue($permissions->hasPermission(PermissionConstants::SEARCH));
        $this->assertFalse($permissions->hasPermission(PermissionConstants::CREATE));
    }
}