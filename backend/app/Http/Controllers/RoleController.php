<?php

namespace App\Http\Controllers;

use App\Http\Requests\RolePermission\RolePermissionRequest;
use App\Http\Requests\RolePermission\RoleRequest;
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
        $role = $this->roleService->store($roleRequest);
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

    public function givePermission(RolePermissionRequest $rolePermissionRequest): JsonResponse
    {
        $role = $this->roleService->givePermission($rolePermissionRequest);
        return (new JsonResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function revokePermission(RolePermissionRequest $rolePermissionRequest): JsonResponse
    {
        $role = $this->roleService->revokePermission($rolePermissionRequest);
        return (new JsonResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getByName(RoleRequest $roleRequest): JsonResponse
    {
        $role = $this->roleService->getByName($roleRequest->name);
        return (new JsonResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getById(Request $request): JsonResponse
    {
        $role = $this->roleService->getById($request->id);
        return (new JsonResource($role))
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
