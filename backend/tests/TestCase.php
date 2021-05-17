<?php

namespace Tests;

use App\Enums\Roles;
use App\Models\User;
use App\Services\Admin\AdminService;
use Exception;
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
        $sa_id = (new Roles)->SUPER_ADMIN_ID();
        if($rol_id == $sa_id) {
            $users = User::all();
            foreach ($users as $key => $user) {
                if($user->isA($sa_id)) {
                    return $user;
                }
            }
            throw new Exception("user superadmin doesn't exist");
        } else {
            $user = User::factory()->create();
            $this->setOrganization($user, $entity, $rol_id);
        }
        return $user;
    }
}
