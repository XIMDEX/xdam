<?php

namespace App\Http\Controllers;

use App\Enums\CDNControllerAction;
use App\Http\Requests\CDNRequest;
use App\Models\Collection;
use App\Services\CDNService;
use App\Services\ResourceService;
use App\Services\UserService;
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
     * @var UserService
     */
    private $userService;

    /**
     * CategoryController constructor.
     * @param CDNService $cdnService
     * @param ResourceService $resourceService
     * @param UserService $userService
     */
    public function __construct(CDNService $cdnService, ResourceService $resourceService, UserService $userService)
    {
        $this->cdnService = $cdnService;
        $this->resourceService = $resourceService;
        $this->userService = $userService;
    }

    public function createCDN(CDNRequest $request)
    {
        if (!isset($request->name))
            return response(['error' => 'The name hasn\'t been provided.']);
        
        if ($this->cdnService->createCDN($request->name))
            return response(['created' => true], Response::HTTP_OK);

        return response(['cdn_created' => false], Response::HTTP_OK);
    }

    public function removeCDN(CDNRequest $request)
    {
        if (!isset($request->cdn_id))
            return response(['error' => 'The CDN ID hasn\'t been provided.']);
        
        if (!$this->cdnService->existsCDN($request->cdn_id))
            return response(['error' => 'The CDN doesn\'t exist.']);
        
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
        
        if (!$this->cdnService->isCollectionAccessible($resource, $cdn))
            return response(['error' => 'The collection isn\'t accessible for this CDN.']);

        $result = $this->cdnService->generateDamResourceHash($cdn, $resource, $request->collection_id);
        return response(['resource_hash' => $result], Response::HTTP_OK);
    }

    public function createMultipleCDNResourcesHash(CDNRequest $request)
    {
        if (!isset($request->resource_ids) || !isset($request->collection_id))
            return response(['error' => 'The resource IDs and the collection ID must be provided.']);

        return $this->manageMultipleResourcesHashCreation($request, false);
    }

    public function createCDNCollectionResourcesHash(CDNRequest $request)
    {
        if (!isset($request->collection_id))
            return response(['error' => 'The collection ID must be provided.']);

        return $this->manageMultipleResourcesHashCreation($request, true);
    }

    private function manageMultipleResourcesHashCreation(CDNRequest $request, bool $fromCollection)
    {
        if (($cdn = $this->cdnService->getCDNInfo($request->cdn_code)) === null)
            return response(['error' => 'The CDN doesn\'t exist.']);
        
        if (($collection = $this->resourceService->getCollection($request->collection_id)) === null)
            return response(['error' => 'The collection doesn\'t exist.']);

        if (!$this->cdnService->isCollectionAccessible_v2($collection, $cdn))
            return response(['error' => 'The collection isn\'t accessible for this CDN.']);

        $result = [];

        if ($fromCollection) {
            $result = $this->cdnService->generateCollectionDamResourcesHash($cdn, $collection);
        } else {
            $result = $this->cdnService->generateMultipleDamResourcesHash($cdn, $request->resource_ids, $request->collection_id);
        }

        return response(['resources' => $result], Response::HTTP_OK);
    }

    public function addCollection(CDNRequest $request)
    {
        return $this->manageCDNCollection($request, CDNControllerAction::ADD_COLLECTION);
    }

    public function removeCollection(CDNRequest $request)
    {
        return $this->manageCDNCollection($request, CDNControllerAction::REMOVE_COLLECTION);
    }

    public function checkCollection(CDNRequest $request)
    {
        return $this->manageCDNCollection($request, CDNControllerAction::CHECK_COLLECTION);
    }

    public function listCollections(CDNRequest $request)
    {
        return $this->manageCDNCollection($request, CDNControllerAction::LIST_COLLECTIONS);
    }

    private function manageCDNCollection(CDNRequest $request, string $action)
    {
        if ($action !== CDNControllerAction::LIST_COLLECTIONS) {
            if (!isset($request->cdn_id) || !isset($request->collection_id))
                return response(['error' => 'The CDN ID and the collection ID must be provided.'], Response::HTTP_BAD_REQUEST);
        } else {
            if (!isset($request->cdn_id))
                return response(['error' => 'The CDN ID must be provided.'], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->cdnService->existsCDN($request->cdn_id))
            return response(['error' => 'The CDN ID doesn\'t exist.'], Response::HTTP_BAD_REQUEST);

        if ($action !== CDNControllerAction::LIST_COLLECTIONS)
            if (!$this->cdnService->existsCollection($request->collection_id))
                return response(['error' => 'The collection ID doesn\'t exist.'], Response::HTTP_BAD_REQUEST);
    
        $result = [];

        if ($action === CDNControllerAction::REMOVE_COLLECTION) {
            $result = [
                'collection_removed' => $this->cdnService->removeCDNCollection($request->cdn_id, $request->collection_id)
            ];
        } else if ($action === CDNControllerAction::ADD_COLLECTION) {
            $result = [
                'collection_added' => $this->cdnService->addCDNCollection($request->cdn_id, $request->collection_id)
            ];
        } else if ($action === CDNControllerAction::CHECK_COLLECTION) {
            $result = [
                'collection_exists' => $this->cdnService->checkCDNCollection($request->cdn_id, $request->collection_id)
            ];
        } else if ($action === CDNControllerAction::LIST_COLLECTIONS) {
            $result = [
                'collections' => $this->cdnService->getCDNCollections($request->cdn_id)
            ];
        } else {
            return response(['error' => 'Bad request'], Response::HTTP_BAD_REQUEST);
        }

        return response($result, Response::HTTP_OK);
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
        return $this->manageAccessPermissionRule($request, false);
    }

    public function removeAccessPermissionRule(CDNRequest $request)
    {
        return $this->manageAccessPermissionRule($request, true);
    }

    private function manageAccessPermissionRule(CDNRequest $request, bool $toRemove)
    {
        $key = $toRemove ? 'access_permission_rule_removal' : 'access_permission_rule_addition';

        if (!isset($request->cdn_id) || !isset($request->access_permission) || !isset($request->rule))
            return response([$key => false, 'error' => 'The CDN ID, the access permission type, and the rule must be provided.']);

        if (!$this->cdnService->existsCDN($request->cdn_id))
            return response([$key => false, 'error' => 'The CDN ID doesn\'t exist.']);

        if (!$this->cdnService->existsAccessPermissionType($request->access_permission))
            return response([$key => false, 'error' => 'The access permission type must be valid.']);

        $result = $this->cdnService->manageAccessPermissionRule($request->cdn_id,
                    $request->access_permission, $request->rule, $key, $toRemove);

        return response($result, Response::HTTP_OK);
    }
}