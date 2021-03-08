<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleAbility\DeleteRoleRequest;
use App\Http\Requests\RoleAbility\RoleAbilityRequest;
use App\Http\Requests\RoleAbility\RoleRemoveAbilityRequest;
use App\Http\Requests\RoleAbility\RoleStoreRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Organization;
use Silber\Bouncer\Database\Role;
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
        $role = $this->roleService->store($organization, $roleRequest->name, $roleRequest->title, $roleRequest->entity_type);
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

    public function setAbilityToRole(Organization $organization, RoleAbilityRequest $roleAbilityRequest): JsonResponse
    {
        $role = $this->roleService->setAbilityToRole($roleAbilityRequest->role_id, $roleAbilityRequest->ability_ids, $roleAbilityRequest->action);
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
        $role = $this->roleService->update($request->role_id, $request->name);
        return (new JsonResource([$role]))
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
