<?php

namespace App\Http\Controllers;

use App\Http\Requests\Workspace\GetWorkspaceRequest;
use App\Http\Requests\Workspace\ListWorkspacesRequest;
use App\Http\Requests\Workspace\CreateWorkspaceRequest;
use App\Http\Requests\Workspace\DeleteWorkspaceRequest;
use App\Http\Requests\Workspace\UpdateWorkspaceRequest;
use App\Http\Resources\WorkspaceCollection;
use App\Http\Resources\WorkspaceResource;
use App\Services\OrganizationWorkspace\WorkspaceService;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class WorkspaceController extends Controller
{

    /**
     * @var WorkspaceService
     */
    private $workspaceService;

    /**
     * WorkspaceController constructor.
     * @param WorkspaceService $workspaceService
     */

    public function __construct(WorkspaceService $workspaceService)
    {
        $this->workspaceService = $workspaceService;
    }

    public function index(ListWorkspacesRequest $request)
    {
        $wsps = $this->workspaceService->index();
        return (new WorkspaceCollection($wsps))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function get(GetWorkspaceRequest $request)
    {
        $wsp = $this->workspaceService->get($request->workspace_id);
        return (new WorkspaceResource($wsp))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function create(CreateWorkspaceRequest $request)
    {
        $wsp = $this->workspaceService->create($request->organization_id, $request->name);
        return (new WorkspaceResource($wsp))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function delete(DeleteWorkspaceRequest $request)
    {
        $wsp = $this->workspaceService->delete($request->workspace_id);
        return (new JsonResource($wsp))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function update(UpdateWorkspaceRequest $request)
    {
        $wsp = $this->workspaceService->update($request->workspace_id, $request->name);
        return (new JsonResource($wsp))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
