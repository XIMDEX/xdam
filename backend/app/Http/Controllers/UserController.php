<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{

    /**
     * @var UserService
     */
    private $userService;

    /**
     * RoleController constructor.
     * @param RoleService $roleService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
        /**
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function userAuth()
    {
        $userResource = $this->userService->user_auth();
        return (new JsonResource($userResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function userModel()
    {
        $userResource = $this->userService->user_model();
        return (new UserResource($userResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
