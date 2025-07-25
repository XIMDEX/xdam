<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetOrganizationResourcesRequest;
use App\Http\Requests\Workspace\CreateWorkspaceRequest;
use App\Http\Requests\Workspace\DeleteWorkspaceRequest;
use App\Http\Requests\Workspace\GetWorkspaceRequest;
use App\Http\Requests\Workspace\ListWorkspacesRequest;
use App\Http\Requests\Workspace\UpdateWorkspaceRequest;
use App\Http\Requests\Workspace\GetMultipleWorkspacesRequest;
use App\Http\Resources\ResourceCollection;
use App\Http\Resources\ResourceResource;
use App\Http\Resources\WorkspaceCollection;
use App\Http\Resources\WorkspaceInfoResource;
use App\Http\Resources\WorkspaceResource;
use App\Models\Collection;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use App\Services\OrganizationWorkspace\WorkspaceService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection as JsonResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WorkspaceController extends Controller
{

    /**
     * @var WorkspaceService
     */
    private WorkspaceService $workspaceService;

    /**
     * WorkspaceController constructor.
     * 
     * @param WorkspaceService $workspaceService
     */
    public function __construct(WorkspaceService $workspaceService)
    {
        $this->workspaceService = $workspaceService;
    }

    public function index(ListWorkspacesRequest $request)
    {
        $wsps = $this->workspaceService->index($request->org);
        if ($request->boolean('lite', false)) {
            return WorkspaceInfoResource::collection($wsps)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        }
        return WorkspaceResource::collection($wsps)
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
        $existingWorkspace = Workspace::where('name', $request->name)->where('id', '!=', $request->workspace_id)->first();

        if ($existingWorkspace) {
            return response()->json(['message' => 'Workspace with this name already exists.'], 400);
        }

        $wsp = $this->workspaceService->update($request->workspace_id, $request->name);

    return (new JsonResource($wsp))
        ->response()
        ->setStatusCode(Response::HTTP_OK);
    }

    public function getResources(GetWorkspaceRequest $request)
    {
        try {
            $wsp = $this->workspaceService->getResources($request->workspace_id);
            
            if ($wsp === null) {
                throw new NotFoundHttpException('Workspace not found.');
            }
    
            return (new JsonResource($wsp))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (NotFoundHttpException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching resources.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

    public function getMultiple(GetMultipleWorkspacesRequest $request)
    {
        $workspacesId = $request->workspacesId;
        $workspaces = $this->workspaceService->getMultpleWorkspaces($workspacesId);

        return (new JsonResource($workspaces))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
