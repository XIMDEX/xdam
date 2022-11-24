<?php

namespace App\Http\Controllers;

use App\Enums\ResourceType;
use App\Models\DamResource;
use App\Services\SemanticService;
use App\Services\Tika\TikaResourceService;
use App\Http\Resources\ResourceResource;
use App\Http\Resources\ResourceCollection;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Http\Requests\PatchResourceRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SemanticController extends Controller
{
    /**
     * @var SemanticService
     */
    private SemanticService $semanticService;

    /**
     * @var TikaResourceService
     */
    private TikaResourceService $resourceService;

    /**
     * SemanticController constructor
     * @param SemanticService $semanticService
     * @param TikaResourceService $resourceService
     */
    public function __construct(SemanticService $semanticService, TikaResourceService $resourceService)
    {
        $this->semanticService = $semanticService;
        $this->resourceService = $resourceService;
    }

    /**
     * @param Request $request
     * @return String
     */
    public function enhance(Request $request)
    {
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
    public function enhanceAutomatic(Request $request)
    {
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

    public function enhanceResources(Request $request)
    {
        $resources = DamResource::get();
        $this->manageResourcesEnhancement($resources);
        return new JsonResponse([], Response::HTTP_OK);
    }

    public function enhanceDocuments(Request $request)
    {
        $documents = $this->resourceService->getByType(ResourceType::document());
        $this->manageResourcesEnhancement($documents);
        return new JsonResponse([], Response::HTTP_OK);
    }

    private function manageResourcesEnhancement($resources)
    {
        foreach ($resources as $resource) {
            $this->resourceService->getMediaAttached($resource);
        }
    }
}