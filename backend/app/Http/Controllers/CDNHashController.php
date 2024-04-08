<?php

namespace App\Http\Controllers;

use App\Http\Requests\CDNHashResourceRequest;
use App\Models\CDN;
use Illuminate\Http\Request;
use App\Services\CDNService;
use App\Services\ResourceService;
use App\Services\UserService;
use Symfony\Component\HttpFoundation\Response;


class CDNHashController extends Controller
{
    /**
     * @var CDNService
     */
    private $cdnService;

    /**
     * @var ResourceService
     */
    private $resourceService;

    /**
     * CategoryController constructor.
     * @param CDNService $cdnService
     * @param ResourceService $resourceService
     * @param UserService $userService
     */
    public function __construct(CDNService $cdnService, ResourceService $resourceService)
    {
        $this->cdnService = $cdnService;
        $this->resourceService = $resourceService;
    }
    public function createCDNResourceHash(CDNHashResourceRequest $request)
    {
        if (($cdn = $this->cdnService->getCDNInfo($request->cdn_code)) === null){
            return response(['error' => 'The CDN doesn\'t exist.']);
        }

        $resource = $this->resourceService->getResource($request->resource_id, $request->collection_id);
        if ($resource === null) {
            return response(['error' => 'The resource doesn\'t exist.'], Response::HTTP_NOT_FOUND);
        }
    
        if (!$this->cdnService->isCollectionAccessible($resource, $cdn)) {
            return response(['error' => 'The collection isn\'t accessible for this CDN.'], Response::HTTP_FORBIDDEN);
        }
    
        $result = $this->cdnService->generateDamResourceHash($cdn, $resource, $request->collection_id);
        return response(['resource_hash' => $result], Response::HTTP_OK);
    }

    public function createMultipleCDNResourcesHash(CDNHashResourceRequest $request)
    {
        $cdn = $this->cdnService->getCDNInfo($request->cdn_code);
        $result = $this->cdnService->generateMultipleDamResourcesHash($cdn , $request->resource_id, $request->collection_id);
    
        if ($result === null) {
            return response(['error' => 'Failed to generate resource hash.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    
        return response(['resources' => $result], Response::HTTP_OK);

    }

    public function generateCollectionDamResourcesHash($request)
    {
        $cdn = $this->cdnService->getCDNInfo($request->cdn_code);
        $results = $this->cdnService->generateMultipleDamResourcesHash($cdn, $request->resource_ids, $request->collection_id);
        return $results;
    }


    private function manageMultipleResourcesHashCreation(CDNHashResourceRequest $request, bool $fromCollection)
    {
        $result = [];

        if ($fromCollection) {
            $result = $this->cdnService->generateCollectionDamResourcesHash($cdn, $collection);
        } else {
            $result = $this->cdnService->generateMultipleDamResourcesHash($cdn, $request->resource_ids, $request->collection_id);
        }

        return response(['resources' => $result], Response::HTTP_OK);
    }
}
