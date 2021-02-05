<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleAbility\RoleAbilityRequest;
use App\Http\Requests\RoleAbility\RoleRemoveAbilityRequest;
use App\Http\Requests\RoleAbility\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;


class RoleController extends Controller
{

    /**
     * @var RoleService
     */
    private $roleService;

    /**
     * RoleController constructor.
     * @param RoleService $roleService
     */
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function store(RoleRequest $roleRequest): JsonResponse
    {
        $role = $this->roleService->store($roleRequest->name, $roleRequest->title);
        return (new JsonResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function index(): JsonResponse
    {
        $roles = $this->roleService->index();
        return (new JsonResource($roles))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function giveAbility(RoleAbilityRequest $roleAbilityRequest): JsonResponse
    {
        $role = $this->roleService->giveAbility($roleAbilityRequest->role_id, $roleAbilityRequest->ability, $roleAbilityRequest->title, $roleAbilityRequest->ability_id);
        return (new JsonResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function removeAbility(RoleRemoveAbilityRequest $roleAbilityRequest): JsonResponse
    {
        $role = $this->roleService->removeAbility($roleAbilityRequest->role_id, $roleAbilityRequest->ability_id);
        return (new JsonResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }


    public function get(Request $request): JsonResponse
    {
        $role = $this->roleService->get($request->id);
        return (new RoleResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function update(Request $request): JsonResponse
    {
        $role = $this->roleService->update($request->id);
        //update logic
        return (new JsonResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function delete(Request $request): JsonResponse
    {
        $role = $this->roleService->delete($request->id);
        return (new JsonResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
