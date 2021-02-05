<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationWorkspace\CreateOrganizationRequest;
use App\Http\Resources\OrganizationCollection;
use App\Services\OrganizationWorkspace\OrganizationService;
use Illuminate\Http\Request;
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

    public function index()
    {
        $orgs = $this->organizationService->index();
        return (new OrganizationCollection($orgs))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function get(Request $request)
    {
        $orgs = $this->organizationService->get($request);
        return (new JsonResource($orgs))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function create(CreateOrganizationRequest $request)
    {
        $org = $this->organizationService->create($request);
        return (new JsonResource($org))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function delete(Request $request)
    {
        $org = $this->organizationService->delete($request->id);
        return (new JsonResource($org))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

}
