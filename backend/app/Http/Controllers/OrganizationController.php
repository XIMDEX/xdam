<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCollectionRequest;
use App\Http\Requests\Organization\CreateOrganizationRequest;
use App\Http\Requests\Organization\DeleteOrganizationRequest;
use App\Http\Requests\Organization\GetOrganizationRequest;
use App\Http\Requests\Organization\ListOrganizationsRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationCollection;
use App\Services\OrganizationWorkspace\OrganizationService;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class OrganizationController extends Controller
{

    /**
     * @var OrganizationService
     */
    private $organizationService;

    /**
     * OrganizationController constructor.
     * @param OrganizationService $organizationService
     */
    public function __construct(OrganizationService $organizationService)
    {
        $this->organizationService = $organizationService;
    }

    public function index(ListOrganizationsRequest $request)
    {
        $orgs = $this->organizationService->index();
        return (new OrganizationCollection($orgs))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function get(GetOrganizationRequest $request)
    {
        $orgs = $this->organizationService->get($request->organization_id);
        return (new JsonResource($orgs))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function create(CreateOrganizationRequest $request)
    {
        $org = $this->organizationService->create($request->name);
        return (new JsonResource($org))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function createCollection(CreateCollectionRequest $request)
    {
        $collection = $this->organizationService->createCollection($request->organization_id, $request->name, $request->type_id);
        return (new JsonResource($collection))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function indexCollections()
    {
        $collection = $this->organizationService->indexCollections();
        return (new JsonResource($collection))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function delete(DeleteOrganizationRequest $request)
    {
        $org = $this->organizationService->delete($request->organization_id);
        return (new JsonResource($org))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function update(UpdateOrganizationRequest $request)
    {
        $org = $this->organizationService->update($request->organization_id, $request->name);
        return (new JsonResource($org))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

}
