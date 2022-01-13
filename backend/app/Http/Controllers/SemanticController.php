<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ResourceResource;
use App\Services\SemanticService;
use App\Services\ResourceService;
use App\Http\Requests\StoreResourceRequest;
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

        $resourceToStore = $this->semanticService->enhance($request->all());

        $resource = $this->resourceService->store($resourceToStore);
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
}
