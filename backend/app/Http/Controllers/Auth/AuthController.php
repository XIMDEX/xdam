<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Http\Requests\Auth\SignUpRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    private $authService;

    /**
     * AuthController constructor.
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function login(LoginRequest $loginRequest)
    {
        $authResource = $this->authService->login($loginRequest->input());
        return (new AuthResource($authResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function signup(SignUpRequest $signUpRequest)
    {
        $authResource = $this->authService->signup($signUpRequest->input());
        return (new AuthResource($authResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function logout()
    {
        $authResource = $this->authService->logout();
        return (new JsonResource($authResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function user()
    {
        $authResource = $this->authService->user();
        return (new JsonResource($authResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
