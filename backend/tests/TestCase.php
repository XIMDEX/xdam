<?php

namespace Tests;

use App\Enums\Roles;
use App\Models\User;
use App\Services\Admin\AdminService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setOrganization($user, $org, $role_id, $only_organization = true)
    {
        $adminService = new AdminService();
        $adminService->setOrganizationHelper($user, $org, $role_id, $only_organization);
    }

    public function getUserWithRole($rol_id, $entity)
    {
        if($rol_id == Roles::super_admin_id) {
            $user = User::find(1);
        } else {
            $user = User::factory()->create();
            $this->setOrganization($user, $entity, $rol_id, false);
        }
        return $user;
    }
}
