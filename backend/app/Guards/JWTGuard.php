<?php

namespace App\Guards;

use App\Models\User;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWT;
use Illuminate\Support\Facades\Auth;

class JWTGuard implements Guard
{
    use GuardHelpers;

    /**
     * @var JWT $jwt
     */
    protected JWT $jwt;
    /**
     * @var Request $request
     */
    protected Request $request;
    /**
     * JWTGuard constructor.
     * @param JWT $jwt
     * @param Request $request
     */
    public function __construct(JWT $jwt, Request $request)
    {
        $this->jwt = $jwt;
        $this->request = $request;
    }

    public function user()
    {
        $this->jwt->setRequest($this->request)->getToken();
        $check = $this->jwt->check();

        if ($check) {
            $id = $this->jwt->payload()->get('sub');

            // $this->user = new User();
            // $this->user->id = getenv('SUPER_ADMIN_USER_ID');
            
            $this->user = User::find(getenv('SUPER_ADMIN_USER_ID'));
            $this->user->selected_workspace = getenv('WORKSPACE_ID');
            $this->user->save();

            $this->user->xdirRoles = $this->jwt->payload()->get('roles');

            return $this->user;
        }
        return null;
    }

    public function attempt() {
        return true;
    }

    public function validate(array $credentials = [])
    {
    }
}
