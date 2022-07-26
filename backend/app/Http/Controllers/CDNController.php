<?php

namespace App\Http\Controllers;

use App\Http\Requests\CDNRequest;
use App\Services\CDNService;
use App\Services\ResourceService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class CDNController extends Controller
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
     */
    public function __construct(CDNService $cdnService, ResourceService $resourceService)
    {
        $this->cdnService = $cdnService;
        $this->resourceService = $resourceService;
    }

    public function createCDN(CDNRequest $request)
    {
        if (!isset($request->name)) return response(['error' => 'The name hasn\'t been provided.']);
        if ($this->cdnService->createCDN($request->name))
            return response(['created' => true], Response::HTTP_OK);

        return response(['cdn_created' => false], Response::HTTP_OK);
    }

    public function removeCDN(CDNRequest $request)
    {
        if (!isset($request->cdn_id)) return response(['error' => 'The CDN ID hasn\'t been provided.']);
        if (!$this->cdnService->existsCDN($request->cdn_id)) return response(['error' => 'The CDN doesn\'t exist.']);
        $res = $this->cdnService->removeCDN($request->cdn_id);
        return response(['cdn_removed' => $res], Response::HTTP_OK);
    }

    public function createCDNResourceHash(CDNRequest $request)
    {
        if (!isset($request->resource_id) || !isset($request->collection_id))
            return response(['error' => 'The resource ID and the collection ID must be provided.']);

        if (($cdn = $this->cdnService->getCDNInfo($request->cdn_code)) === null)
            return response(['error' => 'The CDN doesn\'t exist.']);

        if (($resource = $this->resourceService->getResource($request->resource_id, $request->collection_id)) === null)
            return response(['error' => 'The resource doesn\'t exist.']);
        
        $result = $this->cdnService->generateDamResourceHash($cdn, $resource, $request->collection_id);
        return response(['resource_hash' => $result], Response::HTTP_OK);
    }

    public function addCollection(CDNRequest $request)
    {
        if (!isset($request->cdn_id) || !isset($request->collection_id))
            return response(['error' => 'The CDN ID and the collection ID must be provided.']);

        if (!$this->cdnService->existsCDN($request->cdn_id))
            return response(['error' => 'The CDN ID doesn\'t exist.']);

        if (!$this->cdnService->existsCollection($request->collection_id))
            return response(['error' => 'The collection ID doesn\'t exist.']);

        $res = $this->cdnService->addCDNCollection($request->cdn_id, $request->collection_id);
        return response(['collection_added' => $res], Response::HTTP_OK);
    }

    public function removeCollection(CDNRequest $request)
    {
        if (!isset($request->cdn_id) || !isset($request->collection_id))
            return response(['error' => 'The CDN ID and the collection ID must be provided.']);

        $res = $this->cdnService->removeCDNCollection($request->cdn_id, $request->collection_id);
        return response(['collection_removed' => $res], Response::HTTP_OK);
    }

    public function updateAccessPermission(CDNRequest $request)
    {
        if (!isset($request->cdn_id) || !isset($request->access_permission))
            return response(['error' => 'The CDN ID and the access permission type must be provided.']);

        if (!$this->cdnService->existsAccessPermissionType($request->access_permission))
            return response(['error' => 'The access permission type must be valid.']);

        $res = $this->cdnService->updateAccessPermissionType($request->cdn_id, $request->access_permission);
        return response(['access_permission_updated' => $res], Response::HTTP_OK);
    }

    public function addAccessPermissionRule(CDNRequest $request)
    {
        if (!isset($request->cdn_id) || !isset($request->access_permission) 
            || (!isset($request->ip_address) && !isset($request->lti)))
            return response(['error' => 'The CDN ID, the access permission type, and the rule must be provided.']);

        if (!$this->cdnService->existsCDN($request->cdn_id))
            return response(['error' => 'The CDN ID doesn\'t exist.']);

        if (!$this->cdnService->existsAccessPermissionType($request->access_permission))
            return response(['error' => 'The access permission type must be valid.']);

        $res = $this->cdnService->addAccessPermissionRule($request->cdn_id, $request->access_permission,
                                                            $request->ip_address, $request->lti);
        return response(['access_permission_rule_added' => $res], Response::HTTP_OK);
    }

    public function removeAccessPermissionRule(CDNRequest $request)
    {
        if (!isset($request->cdn_id) || !isset($request->access_permission) 
            || (!isset($request->ip_address) && !isset($request->lti)))
            return response(['error' => 'The CDN ID, the access permission type, and the rule must be provided.']);

        if (!$this->cdnService->existsCDN($request->cdn_id))
            return response(['error' => 'The CDN ID doesn\'t exist.']);

        if (!$this->cdnService->existsAccessPermissionType($request->access_permission))
            return response(['error' => 'The access permission type must be valid.']);

        $res = $this->cdnService->removeAccessPermissionRule($request->cdn_id, $request->access_permission,
                                                            $request->ip_address, $request->lti);
        return response(['access_permission_rule_removed' => $res], Response::HTTP_OK);
    }
}