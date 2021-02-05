<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationWorkspace\CreateWorkspaceRequest;
use App\Http\Resources\WorkspaceCollection;
use App\Http\Resources\WorkspaceResource;
use App\Services\OrganizationWorkspace\WorkspaceService;
use Illuminate\Http\Request;
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

    public function index()
    {
        $wsps = $this->workspaceService->index();
        return (new WorkspaceCollection($wsps))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function get(Request $request)
    {
        $wsp = $this->workspaceService->get($request->id);
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

    public function delete(Request $request)
    {
        $wsp = $this->workspaceService->delete($request->id);
        return (new WorkspaceResource($wsp))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
