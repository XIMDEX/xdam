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
        $resource = $this->resourceService->update($damResource, $request->all(), $this->getAvailableResourceSizes());
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
        $resource = $this->resourceService->patch($damResource, $request->all(), $this->getAvailableResourceSizes());
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
        $resource = $this->resourceService->patch($damResource, $semanticRequest, $this->getAvailableResourceSizes());

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

        $response = $this->semanticService->enhanceV2($request->all());
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

    private function getAvailableResourceSizes()
    {
        $sizes = [
            'image' => [
                'allowed_sizes' => ['thumbnail', 'small', 'medium', 'large', 'raw', 'default'],
                'sizes' => [
                    'thumbnail' => array('width' => 256, 'height' => 144),
                    'small'     => array('width' => 426, 'height' => 240),
                    'medium'    => array('width' => 1280, 'height' => 720), 
                    'large'     => array('width' => 1920, 'height' => 1080), //4k 3840x2160 HD 1920x1080
                    'raw'       => 'raw',
                    'default'   => array('width' => 1280, 'height' => 720)
                ],
                'qualities' => [
                    'thumbnail' => 25,
                    'small'     => 25,
                    'medium'    => 50,
                    'large'     => 100,
                    'raw'       => 'raw',
                    'default'   => 90,
                ],
                'error_message' => ''
            ],
            'video' => [
                'allowed_sizes' => ['very_low', 'low', 'standard', 'hd', 'raw', 'thumbnail', 'small', 'medium', 'default'],
                'sizes_scale'   => ['very_low', 'low', 'standard', 'hd'],   // Order Lowest to Greatest
                'screenshot_sizes'  => ['thumbnail', 'small', 'medium'],
                'sizes' => [
                    // 'lowest'        => array('width' => 256, 'height' => 144, 'name' => '144p'),
                    'very_low'      => array('width' => 426, 'height' => 240, 'name' => '240p'),
                    'low'           => array('width' => 640, 'height' => 360, 'name' => '360p'),
                    'standard'      => array('width' => 854, 'height' => 480, 'name' => '480p'),
                    'hd'            => array('width' => 1280, 'height' => 720, 'name' => '720p'),
                    // 'full_hd'       => array('width' => 1920, 'height' => 1080, 'name' => '1080p'),
                    'raw'           => 'raw',
                    //'thumbnail'     => 'thumbnail',
                    'thumbnail'     => array('width' => 256, 'height' => 144, 'name' => '144p'),
                    'small'         => array('width' => 426, 'height' => 240, 'name' => '240p'),
                    'medium'        => array('width' => 854, 'height' => 480, 'name' => '480p'),
                    'default'       => 'raw'
                ],
                'qualities' => [
                    'thumbnail' => 25,
                    'small'     => 25,
                    'medium'    => 50,
                    'raw'       => 'raw',
                    'default'   => 90
                ],
                'error_message' => ''
            ]
        ];

        foreach ($sizes as $k => $v) {
            $sizes[$k]['error_message'] = $this->setErrorMessage($sizes[$k]['allowed_sizes']);
        }

        return $sizes;
    }
}
