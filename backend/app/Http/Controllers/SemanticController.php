<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ResourceResource;
use App\Services\SemanticService;
use App\Services\ResourceService;
use App\Http\Requests\StoreResourceRequest;
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
}
