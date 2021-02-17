<?php

namespace Tests;

use App\Models\User;
use Error;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Silber\Bouncer\Bouncer;
use Silber\Bouncer\BouncerFacade;
use Silber\Bouncer\Database\Role;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function getUserWithRole($rol_id, $entity = null)
    {
        $user = User::factory()->create();
        $rol = Role::find($rol_id);
        if($rol->name == 'admin') {
            BouncerFacade::assign($rol)->to($user);
            return $user;
        }

        $abilities = [];
        foreach ($rol->getAbilities()->toArray() as $ability) {
            $abilities[] = $ability['name'];
        }
        $user->abilities = $abilities;

        if($entity)
            BouncerFacade::allow($user)->to($abilities, $entity);
        else
            BouncerFacade::allow($user)->to($abilities);

        return $user;
    }
}
