<?php

namespace App\Http\Controllers;

use App\Http\Requests\Organization\SetOrganizationsToUserRequest;
use App\Http\Requests\SetRoleAbilitiesOnEntityRequest;
use App\Http\Requests\UnsetOrganizationRequest;
use App\Http\Requests\Workspace\SetWorkspacesToUserRequest;
use App\Services\Admin\AdminService;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{

    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function setOrganizations(SetOrganizationsToUserRequest $request)
    {
        $adminResource = $this->adminService->setOrganizations($request->user_id, $request->organization_id, $request->with_role_id);
        return (new JsonResource($adminResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function setAllWorkspacesOfOrganization(SetOrganizationsToUserRequest $request)
    {
        $adminResource = $this->adminService->setAllWorkspacesOfOrganization($request->user_id, $request->organization_id, $request->with_role_id);
        return (new JsonResource($adminResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function setWorkspaces(SetWorkspacesToUserRequest $request)
    {
        $adminResource = $this->adminService->setWorkspaces($request->user_id, $request->workspace_id, $request->with_role_id);
        return (new JsonResource($adminResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function unsetOrganizations(UnsetOrganizationRequest $request)
    {
        $adminResource = $this->adminService->unsetOrganizations($request->user_id, $request->organization_id);
        return (new JsonResource($adminResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function unsetWorkspaces(SetWorkspacesToUserRequest $request)
    {
        $adminResource = $this->adminService->unsetWorkspaces($request->user_id, $request->workspace_id);
        return (new JsonResource($adminResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function SetRoleAbilitiesOnEntity(SetRoleAbilitiesOnEntityRequest $request) {
        $adminResource = $this->adminService
            ->SetRoleAbilitiesOnEntity($request->user_id, $request->role_id, $request->entity_id, $request->type, $request->on);
        return (new JsonResource($adminResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
