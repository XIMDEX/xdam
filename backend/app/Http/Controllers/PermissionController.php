<?php

namespace App\Http\Controllers;

use App\Http\Requests\RolePermission\PermissionRequest;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class PermissionController extends Controller
{
    /**
     * @var PermissionService
     */
    private $permissionService;

    /**
     * RoleController constructor.
     * @param PermissionService $permissionService
     */
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function store(PermissionRequest $permissionRequest): JsonResponse
    {
        $permission = $this->permissionService->store($permissionRequest);
        return (new JsonResource($permission))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function index(): JsonResponse
    {
        $permissions = $this->permissionService->index();
        return (new JsonResource($permissions))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getByName(PermissionRequest $permissionRequest): JsonResponse
    {
        $peromission = $this->permissionService->getByName($permissionRequest->name);
        return (new JsonResource($peromission))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getById(Request $request): JsonResponse
    {
        $permission = $this->permissionService->getById($request->id);
        return (new JsonResource($permission))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function update(Request $request): JsonResponse
    {
        $permission = $this->permissionService->update($request->id);
        //update logic
        return (new JsonResource($permission))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function delete(Request $request): JsonResponse
    {
        $permission = $this->permissionService->delete($request->id);
        return (new JsonResource($permission))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
