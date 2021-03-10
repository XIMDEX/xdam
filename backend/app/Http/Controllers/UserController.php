<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachResourceToCollectionRequest;
use App\Http\Requests\AttachResourceToWorkspaceRequest;
use App\Http\Requests\SelectOrganizationRequest;
use App\Http\Requests\SelectWorkspaceRequest;
use App\Http\Resources\OrganizationCollection;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\ResourceCollection;
use App\Http\Resources\UserResource;
use App\Http\Resources\WorkspaceCollection;
use App\Models\DamResource;
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
        return (new JsonResource($userResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function userInfo()
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

    public function resourceInfo(DamResource $damResource)
    {
        $resourceInfo = $this->userService->resourceInfo($damResource);
        return (new JsonResource($resourceInfo))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function attachResourceToCollection(AttachResourceToCollectionRequest $request)
    {
        $userResource = $this->userService->attachResourceToCollection($request->collection_id, $request->resource_id, $request->organization_id);
        return (new JsonResource($userResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function attachResourceToWorkspace(AttachResourceToWorkspaceRequest $request)
    {
        $userResource = $this->userService->attachResourceToWorkspace($request->resource_id);
        return (new JsonResource($userResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getOrganizations()
    {
        $organizationResource = $this->userService->getOrganizations();
        return (new OrganizationCollection($organizationResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getWorkspaces()
    {
        $workspacesResource = $this->userService->getWorkspaces();
        return (new WorkspaceCollection($workspacesResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getWorkspacesOfOrganization(Request $request)
    {
        $workspaces = $this->userService->getWorkspacesOfOrganization($request->organization_id);
        return (new WorkspaceCollection($workspaces))
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
