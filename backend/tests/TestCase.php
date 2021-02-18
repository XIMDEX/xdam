<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Silber\Bouncer\Database\Role;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function getUserWithRole($rol_id, $entity = null)
    {
        $user = User::factory()->create();
        $rol = Role::find($rol_id);
        if ($rol->name == 'admin') {
            Bouncer::assign($rol)->to($user);
            return $user;
        }

        $abilities = [];
        foreach ($rol->getAbilities() as $ability) {
            $abilities[] = $ability->name;
        }

        $user->abilities = $abilities;

        if ($entity) {
            $user->organizations()->attach($entity->id);
            Bouncer::allow($user)->to($abilities, $entity);
        } else {
            Bouncer::allow($user)->to($abilities);
        }

        return $user;
    }

}
