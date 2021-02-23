<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachResourceToCollectionRequest;
use App\Http\Requests\SelectOrganizationRequest;
use App\Http\Requests\SelectWorkspaceRequest;
use App\Http\Resources\ResourceCollection;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
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
    public function user()
    {
        $userResource = $this->userService->user();
        return (new UserResource($userResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function resources()
    {
        $userResource = $this->userService->resources();
        return (new ResourceCollection($userResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function attachResourceToCollection(AttachResourceToCollectionRequest $request) {
        $userResource = $this->userService->attachResourceToCollection($request->collection_id, $request->resource_id, $request->organization_id);
        return (new JsonResource($userResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getOrganizations()
    {
        $userResource = $this->userService->getOrganizations();
        return (new JsonResource($userResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getWorkspaces()
    {
        $userResource = $this->userService->getWorkspaces();
        return (new JsonResource($userResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getWorkspacesOfOrganization(Request $request)
    {
        $userResource = $this->userService->getWorkspacesOfOrganization($request->organization_id);
        return (new JsonResource($userResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function selectWorkspace(SelectWorkspaceRequest $request)
    {
        $userResource = $this->userService->selectWorkspace($request->workspace_id);
        return (new JsonResource($userResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
