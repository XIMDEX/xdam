<?php

namespace App\Http\Controllers;

use App\Enums\CDNControllerAction;
use App\Http\Requests\CDNRequest;
use App\Models\Collection;
use App\Services\CDNService;
use App\Services\ResourceService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
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

    /**
     * Creates a CDN
     * @param CDNRequest $request
     * @return ResponseFactory
     */
    public function createCDN(CDNRequest $request)
    {
        try {
            $cdnId = $this->cdnService->createCDN($request->name);
    
            if ($cdnId) {
                return response(['cdn' => $cdnId], Response::HTTP_CREATED);
            } else {
                return response(['error' => 'CDN creation failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $e) {
            Log::error('Error creating CDN: ' . $e->getMessage());
            return response(['error' => 'An error occurred while creating CDN'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Removes a CDN
     * @param CDNRequest $request
     * @return ResponseFactory
     */
    public function removeCDN(Request $request)
    {
        try {
            if (!$this->cdnService->existsCDN($request->cdn_id)) {
                return response(['error' => 'The CDN doesn\'t exist.'], Response::HTTP_NOT_FOUND);
            }

            $result = $this->cdnService->removeCDN($request->cdn_id);

            return response(['cdn_removed' => $result], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error removing CDN: ' . $e->getMessage());
            return response(['error' => 'An error occurred while removing CDN'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

    /**
     * Attaches a collection to a CDN
     * @param CDNRequest $request
     * @return ResponseFactory
     */
    public function addCollection(Request $request)
    {
        return $this->manageCDNCollection($request, CDNControllerAction::ADD_COLLECTION);
    }

    /**
     * Deattaches a collection from a CDN
     * @param CDNRequest $request
     * @return ResponseFactory
     */
    public function removeCollection(CDNRequest $request)
    {
        return $this->manageCDNCollection($request, CDNControllerAction::REMOVE_COLLECTION);
    }

    /**
     * Checks if a collection is attached to a CDN
     * @param CDNRequest $request
     * @return ResponseFactory
     */
    public function checkCollection(CDNRequest $request)
    {
        return $this->manageCDNCollection($request, CDNControllerAction::CHECK_COLLECTION);
    }

    /**
     * Lists the attached collections to a CDN
     * @param CDNRequest $request
     * @return ResponseFactory
     */
    public function listCollections(Request $request)
    {
        return $this->manageCDNCollection($request, CDNControllerAction::LIST_COLLECTIONS);
    }

    /**
     * Manages the CDN collections
     * @param CDNRequest $request
     * @param string $action
     * @return ResponseFactory
     */
    private function manageCDNCollection(Request $request, string $action)
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

    /**
     * Updates the access permissions of a CDN
     * @param CDNRequest $request
     * @return ResponseFactory
     */
    public function updateAccessPermission(CDNRequest $request)
    {
        // Checks if the required data is provided
        if (!isset($request->cdn_id) || !isset($request->access_permissions))
            return response(['error' => 'The CDN ID and the access permission types must be provided.']);

        // Checks if the provided data format is correct
        if (gettype($request->access_permissions) !== 'array')
            return response(['error' => 'The access permission types must be provided as an array.']);

        // Sets an empty array, to avoid duplicate entries
        $permissions = [];

        // Iterates through the permission types, to make checkings
        foreach ($request->access_permissions as $permission) {
            // Checks if the permission type is duplicated
            if (!in_array($permission, $permissions))
                $permissions[] = $permission;

            // Checks that each permission type passed is correct
            if (!$this->cdnService->existsAccessPermissionType($permission))
                return response(['error' => 'The access permission types must be valid.']);
        }

        $res = $this->cdnService->updateAccessPermissionType($request->cdn_id, $permissions);
        return response(['access_permission_updated' => $res], Response::HTTP_OK);
    }

    /**
     * Adds an access permission rule
     * @param CDNRequest $request
     * @return ResponseFactory
     */
    public function addAccessPermissionRule(CDNRequest $request)
    {
        return $this->manageAccessPermissionRule($request, false);
    }

    /**
     * Removes an access permission rule
     * @param CDNRequest $request
     * @return ResponseFactory
     */
    public function removeAccessPermissionRule(CDNRequest $request)
    {
        return $this->manageAccessPermissionRule($request, true);
    }

    /**
     * Manages the access permission rules
     * @param CDNRequest $request
     * @param boolean $toRemove
     * @return ResponseFactory
     */
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

    /**
     * Lists the access permission rules of a CDN
     * @param CDNRequest $request
     * @return ResponseFactory
     */
    public function listAccessPermissionRules(CDNRequest $request)
    {
        $key = 'access_permission_rules_list';

        if (!isset($request->cdn_id))
            return response([$key => false, 'error' => 'The CDN ID must be provided.']);

        if (!$this->cdnService->existsCDN($request->cdn_id))
            return response([$key => false, 'error' => 'The CDN ID doesn\'t exist.']);

        $res = $this->cdnService->getAccessPermissionRules($request->cdn_id);
        return response([$key => true, 'data' => $res]);
    }

    public function getCDNs()
    {
        $cdns = $this->cdnService->getCDNs();
        return response()->json(['cdns' => $cdns], 200);;
    }
}