<?php

namespace Tests;

use App\Models\User;
use Error;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function getUser(iterable $roles = null, array $abilities = null)
    {
        $the_user = null;
        $users = User::all();
        foreach ($users as $user) {
            if($roles && $user->isAn(...$roles) || $user->canAny($abilities)) {
                $the_user = $user;
                break;
            } else {
                throw new Error("NO PERMISSION FOUND");
            }
        }
        return $the_user;
    }
}
