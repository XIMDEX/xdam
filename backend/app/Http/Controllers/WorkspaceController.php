<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetOrganizationResourcesRequest;
use App\Http\Requests\Workspace\CreateWorkspaceRequest;
use App\Http\Requests\Workspace\DeleteWorkspaceRequest;
use App\Http\Requests\Workspace\GetWorkspaceRequest;
use App\Http\Requests\Workspace\ListWorkspacesRequest;
use App\Http\Requests\Workspace\SetResourceWorkspaceRequest;
use App\Http\Requests\Workspace\UpdateWorkspaceRequest;
use App\Http\Requests\Workspace\GetMultipleWorkspacesRequest;
use App\Http\Resources\ResourceCollection;
use App\Http\Resources\ResourceResource;
use App\Http\Resources\WorkspaceCollection;
use App\Http\Resources\WorkspaceResource;
use App\Models\Collection;
use App\Models\Organization;
use App\Models\User;
use App\Services\UserService;
use App\Services\OrganizationWorkspace\WorkspaceService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection as JsonResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WorkspaceController extends Controller
{

    /**
     * @var WorkspaceService
     */
    private WorkspaceService $workspaceService;

    /**
     * @var UserService
     */
    private UserService $userService;

    /**
     * WorkspaceController constructor.
     * 
     * @param WorkspaceService $workspaceService
     * @param UserService $userService
     */
    public function __construct(WorkspaceService $workspaceService, UserService $userService)
    {
        $this->workspaceService = $workspaceService;
        $this->userService = $userService;
    }

    public function index(ListWorkspacesRequest $request)
    {
        $wsps = $this->workspaceService->index($request->org);
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

    public function getResources(GetWorkspaceRequest $request)
    {
        $wsp = $this->workspaceService->getResources($request->workspace_id);
        return (new JsonResource($wsp))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getOrganizationResources(GetOrganizationResourcesRequest $request)
    {
        $res = $this->workspaceService->getOrganizationResources($request->organization_id);
        return (new ResourceCollection($res))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function workspaceOfCollection(Collection $collection)
    {
        $org = $collection->organization()->first();
        if(Auth::user()->organizations()->get()->contains($org)) {
            return Auth::user()->workspaces()->where('organization_id', $org->id)->get();
        }
        return ['error'];
    }

    public function setResource(SetResourceWorkspaceRequest $request)
    {
        if (!$request->checkResourceWorkspaceChangeData())
            return response(['error' => 'The needed data hasn\'t been provided.']);

        $user = $this->userService->user();
        $result = $this->workspaceService->setResourceWorkspace($user, $request->resource_id,
                                                                $request->workspace_id,
                                                                $request->workspace_name);

        return response($result)->setStatusCode(Response::HTTP_OK);
    }

    public function getMultiple(GetMultipleWorkspacesRequest $request)
    {
        $worksapcesId = $request->workspacesId;

        $workspaces = $this->workspaceService->getMultpleWorkspaces($worksapcesId);

        return (new JsonResource($workspaces))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
