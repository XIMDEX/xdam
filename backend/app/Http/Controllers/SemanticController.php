<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ResourceResource;
use App\Http\Resources\ResourceCollection;
use App\Services\SemanticService;
use App\Services\ResourceService;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Http\Requests\PatchResourceRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Models\DamResource;

class SemanticController extends Controller
{

    /**
     * @var SemanticService
     */
    private $semanticService;

    /**
     * @var ResourceService
     */
    private $resourceService;

    /**
     * SemanticController constructor
     * @param SemanticService $semanticService
     */
    public function __construct(SemanticService $semanticService, ResourceService $resourceService) {

        $this->semanticService = $semanticService;
        $this->resourceService = $resourceService;

    }

    /**
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getAll() {
        $resources = $this->resourceService->getAll('document');
        return (new ResourceCollection($resources))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }


    /**
     * @param DamResource $damResource
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function get(DamResource $damResource) {
        $damResource = $this->resourceService->get($damResource);
        return (new ResourceResource($damResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param StoreResourceRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function store(StoreResourceRequest $request)
    {
        if ($request->get('enhancer')) {
            $semanticRequest = $request->all();
            $resourceData = $semanticRequest['data'];
            $semanticRequest['text'] = $resourceData->description->body;
            $enhanced = $this->semanticService->updateWithEnhance($resourceData->description, $semanticRequest);
            $semanticRequest['data'] = $enhanced['resources']['data'];
            $resource = $this->resourceService->store($semanticRequest);
        } else {
            $resource = $this->resourceService->store($request->all());
        }

        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param DamResource $damResource
     * @param UpdateResourceRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(DamResource $damResource, UpdateResourceRequest $request)
    {
        $resource = $this->resourceService->update($damResource, $request->all());
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param DamResource $damResource
     * @param UpdateResourceRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function patch(DamResource $damResource, PatchResourceRequest $request)
    {
        $resource = $this->resourceService->patch($damResource, $request->all());
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }


    /**
     * @param DamResource $damResource
     * @return \Illuminate\Http\Response
     */
    public function delete(DamResource $damResource)
    {
        $res = $this->resourceService->delete($damResource);
        return response(['deleted' => $res], Response::HTTP_OK);
    }

    /**
     * @param DamResource $damResource
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function updateWithEnhancement(DamResource $damResource, Request $request)
    {
        $semanticRequest = $request->all();
        $damResourceData = $damResource->data;
        $semanticRequest['text'] = $damResourceData->description->body;
        $enhanced = $this->semanticService->updateWithEnhance($damResourceData->description, $semanticRequest);
        $semanticRequest['data'] = json_encode($enhanced['resources']['data']);
        $resource = $this->resourceService->patch($damResource, $semanticRequest);

        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return String
     */
    public function fetchDocumentsById(Request $request) {

        $data = $this->semanticService->fetchDocuments($request->all());
        $arrayResources = [];

        foreach ($data['resources'] as $resource) {
            $new_resource = $this->resourceService->store($resource);
            $arrayResources[] = $new_resource->toArray();
        }

        return new JsonResponse(
            [
                'data' => $arrayResources,
                'errors' => $data['errors']
            ],
            Response::HTTP_OK
        );

    }

    /**
     * @param Request $request
     * @return String
     */
    public function fetchDocumentsByUuid(Request $request) {

        $data = $this->semanticService->fetchDocuments($request->all(), true);
        $arrayResources = [];

        foreach ($data['resources'] as $resource) {
            $new_resource = $this->resourceService->store($resource);
            $arrayResources[] = $new_resource->toArray();
        }

        return new JsonResponse(
            [
                'data' => $arrayResources,
                'errors' => $data['errors']
            ],
            Response::HTTP_OK
        );

    }

    /**
     * @param Request $request
     * @return String
     */
    public function enhance(Request $request) {

        $response = $this->semanticService->enhance($request->all());
        $resource = false;
        if (count($response['resources']) == 1){
            $resource = $this->resourceService->store($response['resources'][0]);
        }

        return new JsonResponse(
            [
                'data' => $resource ? $resource->toArray() : [],
                'errors' => $response['errors']
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @param Request $request
     * @return String
     */
    public function enhanceAutomatic(Request $request) {

        $data = $this->semanticService->automaticEnhance($request->all());
        $arrayResources = [];

        foreach ($data['resources'] as $resource) {
            $new_resource = $this->resourceService->store($resource, null, null, false);
            if ($new_resource) $arrayResources[] = $new_resource->toArray();
        }

        return new JsonResponse(
            [
                'data' => $arrayResources,
                'errors' => $data['errors']
            ],
            Response::HTTP_OK
        );
    }
}
