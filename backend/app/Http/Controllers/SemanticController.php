<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ResourceResource;
use App\Services\SemanticService;
use App\Services\ResourceService;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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
    public function getDocumentsById(Request $request) {

        $data = $this->semanticService->getDocuments($request->all());
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
    public function getDocumentsByUuid(Request $request) {

        $data = $this->semanticService->getDocuments($request->all(), true);
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
     * @param $semanticResource
     * @return \Illuminate\Http\Response
     */
    public function delete($semanticResource)
    {
        $semanticResource = $this->resourceService->getById($semanticResource);

        $res = $this->resourceService->delete($semanticResource);
        return response(['deleted' => $res], Response::HTTP_OK);
    }

    /**
     * @param $semanticResource
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update($semanticResource, Request $request)
    {
        $semanticResource = $this->resourceService->getById($semanticResource);

        $semanticRequest = $request->all();
        if (array_key_exists("data", $semanticRequest) && gettype($semanticRequest['data']) == "string") {
            $semanticRequest['data'] = json_decode($semanticRequest['data']);
        } 

        $resource = $this->resourceService->update($semanticResource, $semanticRequest);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param $semanticResource
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function patch($semanticResource, Request $request)
    {
        $semanticResource = $this->resourceService->getById($semanticResource);

        if ($request->get('enhance')) {
            $semanticRequest = $request->all();
            $semanticRequest['text'] = $semanticResource->data->description->body;
            $enhanced = $this->semanticService->updateWithEnhance($semanticResource->data->description, $semanticRequest);
            $semanticRequest['data'] = json_encode($enhanced['resources']['data']);
            $resource = $this->resourceService->patch($semanticResource, $semanticRequest);
        } else {
            $resource = $this->resourceService->patch($semanticResource, $request->all());
        }

        
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }


    public function storeEnhancement(StoreResourceRequest $request) {

        $resource = $this->resourceService->store($request->all());
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
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
