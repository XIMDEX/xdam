<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationWorkspace\SetOrganizationRequest;
use App\Http\Requests\OrganizationWorkspace\SetWorkspaceRequest;

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

    public function setOrganizations(SetOrganizationRequest $request)
    {
        $adminResource = $this->adminService->setOrganizations($request->user_id, $request->organization_ids);
        return (new JsonResource($adminResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function setWorkspaces(SetWorkspaceRequest $request)
    {
        $adminResource = $this->adminService->setWorkspaces($request->user_id, $request->workspace_ids);
        return (new JsonResource($adminResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function unsetOrganizations(SetOrganizationRequest $request)
    {
        $adminResource = $this->adminService->unsetOrganizations($request->user_id, $request->organization_ids);
        return (new JsonResource($adminResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function unsetWorkspaces(SetWorkspaceRequest $request)
    {
        $adminResource = $this->adminService->unsetWorkspaces($request->user_id, $request->workspace_ids);
        return (new JsonResource($adminResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

}
