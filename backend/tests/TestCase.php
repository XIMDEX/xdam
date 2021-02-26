<?php

namespace Tests;

use App\Enums\Roles;
use App\Models\User;
use App\Services\Admin\AdminService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Silber\Bouncer\Database\Role;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setOrganization($user, $org, $role_id, $only_organization = false)
    {
        $adminService = new AdminService();
        $adminService->setOrganizationHelper($user, $org, $role_id, $only_organization);
    }

    public function getUserWithRole($rol_id, $entity = null)
    {
        $user = User::factory()->create();
        $rol = Role::find($rol_id);
        if ($rol->name == Roles::super_admin) {
            Bouncer::assign($rol)->to($user);
            return $user;
        }

        $abilities = [];
        foreach ($rol->getAbilities() as $ability) {
            $abilities[] = $ability->name;
        }

        if ($entity) {
            $user->organizations()->attach($entity->id);
            Bouncer::allow($user)->to($abilities, $entity);
        } else {
            Bouncer::allow($user)->to($abilities);
        }

        return $user;
    }
}
