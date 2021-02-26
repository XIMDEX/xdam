<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleAbility\DeleteRoleRequest;
use App\Http\Requests\RoleAbility\RoleAbilityRequest;
use App\Http\Requests\RoleAbility\RoleRemoveAbilityRequest;
use App\Http\Requests\RoleAbility\RoleStoreRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Organization;
use App\Models\Role;
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

    public function store(Organization $organization, RoleStoreRequest $roleRequest): JsonResponse
    {
        $role = $this->roleService->store($organization, $roleRequest->name, $roleRequest->title);
        return (new JsonResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function index(Organization $organization)
    {
        $roles = $this->roleService->index($organization);
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


    public function get(Organization $organization, $role_id): JsonResponse
    {
        $role = $this->roleService->get($organization, $role_id);
        return (new RoleResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function update(Organization $organization, UpdateRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->update($organization, $request->id, $request->data);
        return (new JsonResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function delete(Organization $organization, DeleteRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->delete($organization, $request->id);
        return (new JsonResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
