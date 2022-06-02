<?php

namespace App\Http\Controllers\v2;

use App\Http\Requests\Organization\GetOrganizationFromUuidRequest;
use App\Services\OrganizationWorkspace\OrganizationService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;

final class OrganizationController extends Controller
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


    public function getOrganizationFromId(GetOrganizationFromUuidRequest $request)
    {
        return $this->organizationService->findByXdirId($request->organization_id);
    }

    public function getOrganizationCollections(GetOrganizationFromUuidRequest $request)
    {
        $organization = $this->organizationService->findByXdirId($request->organization_id);

        return $this->organizationService->indexCollections($organization->id);
    }
}
