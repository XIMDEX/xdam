<?php

namespace Tests;

use App\Enums\Roles;
use App\Models\User;
use App\Services\Admin\AdminService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setOrganization($user, $org, $role_id)
    {
        $adminService = new AdminService(new Roles);
        $adminService->setOrganizations($user->id, $org->id, $role_id);
    }

    public function getUserWithRole($rol_id, $entity)
    {
        if($rol_id == (new Roles)->SUPER_ADMIN_ID()) {
            $user = User::find(1);
        } else {
            $user = User::factory()->create();
            $this->setOrganization($user, $entity, $rol_id);
        }
        return $user;
    }
}
