<?php

namespace App\Http\Controllers;

use App\Enums\ResourceType;
use App\Enums\ThumbnailTypes;
use App\Http\Requests\addFileToResourceRequest;
use App\Http\Requests\addPreviewToResourceRequest;
use App\Http\Requests\addUseRequest;
use App\Http\Requests\CDNRequest;
use App\Http\Requests\GetDamResourceRequest;
use App\Http\Requests\ResouceCategoriesRequest;
use App\Http\Requests\SetTagsRequest;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Http\Resources\ExploreCoursesCollection;
use App\Http\Resources\ResourceCollection;
use App\Http\Resources\ResourceResource;
use App\Models\Category;
use App\Services\CDNService;
use App\Models\DamResource;
use App\Models\DamResourceUse;
use App\Models\Media;
use App\Services\MediaService;
use App\Services\ResourceService;
use App\Utils\DamUrlUtil;
use Error;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mimey\MimeTypes;
use Symfony\Component\HttpFoundation\Response;

class ResourceController extends Controller
{
    /**
     * @var ResourceService
     */
    private $resourceService;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var CDNService
     */
    private $cdnService;

    /**
     * CategoryController constructor.
     * @param ResourceService $resourceService
     * @param MediaService $mediaService
     * @param CDNService $cdnService
     */
    public function __construct(ResourceService $resourceService, MediaService $mediaService, CDNService $cdnService)
    {
        $this->resourceService = $resourceService;
        $this->mediaService = $mediaService;
        $this->cdnService = $cdnService;
    }

    public function resourcesSchema ()
    {
        $schemas = $this->resourceService->resourcesSchema();
        return response()->json($schemas);
    }

    public function lomesSchema ()
    {
        return response()->json($this->resourceService->lomesSchema());
    }

    public function lomSchema ()
    {
        return response()->json($this->resourceService->lomSchema());
    }

    private function getThumbnailBySize($size, $media)
    {
        if (null !== $size) {
            $supportedThumbnails = ThumbnailTypes::getValues();
            preg_match_all('!\d+!', $size, $matches);
            $thumbSize = implode("x", $matches[0]);
            foreach ($supportedThumbnails as $supportedThumbnail) {
                if (strpos($supportedThumbnail, $thumbSize) !== false) {
                    return $media->getPath($supportedThumbnail);
                }
            }
        }
        return false;
    }

    /**
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getAll(Request $request)
    {
        $resources = $this->resourceService->getAll(null, $request->get('ps', ResourceService::PAGE_SIZE));
        return (new ResourceCollection($resources))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param DamResource $damResource
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function get(DamResource $damResource, GetDamResourceRequest $getDamResourceRequest)
    {
        $damResource = $this->resourceService->get($damResource);
        return (new ResourceResource($damResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param ResouceCategoriesRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function exploreCourses(ResouceCategoriesRequest $request)
    {
        return (new ExploreCoursesCollection($this->resourceService->exploreCourses()))
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
     * @param StoreResourceRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function store(StoreResourceRequest $request)
    {
        $resource = $this->resourceService->store($request->all());
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function storeBatch(Request $request)
    {
        $resources = $this->resourceService->storeBatch($request->all());
        return (new ResourceCollection($resources))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function setLomesData(DamResource $damResource,  Request $request) {
        $resource = $this->resourceService->setLomesData($damResource, $request);
        return (new JsonResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function setLomData(DamResource $damResource, Request $request)
    {
        $resource = $this->resourceService->setLomData($damResource, $request);
        return (new JsonResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getLomesData(DamResource $damResource) {
        $resource = $this->resourceService->getLomesData($damResource);
        return (new JsonResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getLomData(DamResource $damResource)
    {
        $resource = $this->resourceService->getLomData($damResource);
        return (new JsonResource($resource))
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
     * @param addPreviewToResourceRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function addPreview(DamResource $damResource, addPreviewToResourceRequest $request)
    {
        $resource = $this->resourceService->addPreview($damResource, $request->all());
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param DamResource $damResource
     * @param addFileToResourceRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function addFile(DamResource $damResource, addFileToResourceRequest $request)
    {
        $resource = $this->resourceService->addFile($damResource, $request->all());
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }


    /**
     * @param DamResource $damResource
     * @param SetTagsRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function setTags(DamResource $damResource, setTagsRequest $request)
    {
        $resource = $this->resourceService->setTags($damResource, $request->json()->all()["tags"]);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }


    /**
     * @param DamResource $damResource
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Exception
     */
    public function addCategory(DamResource $damResource, Category $category)
    {
        $is_course = ($damResource->type === 'course');
        $resource = $is_course ? $this->resourceService->setOnlyOneCategoryTo($damResource, $category) :$this->resourceService->addCategoryTo($damResource, $category);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param DamResource $damResource
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Exception
     */
    public function deleteCategory(DamResource $damResource, Category $category)
    {
        $resource = $this->resourceService->deleteCategoryFrom($damResource, $category);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param $damUrl
     * @param null $size
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Exception
     */
    public function render($damUrl, $size = null)
    {
        return $this->renderResource($damUrl, $size);
    }

    private function renderResource($damUrl, $size = null)
    {
        $mediaId = DamUrlUtil::decodeUrl($damUrl);
        $media = Media::findOrFail($mediaId);
        $mediaFileName = explode('/', $media->getPath());
        $mediaFileName = $mediaFileName[count($mediaFileName) - 1];

        $mimeType = $media->mime_type;
        $fileType = explode('/', $mimeType)[0];

        if($fileType == 'video' || $fileType == 'image') {
            $size = $this->getResourceSize($fileType, $size);
            $compressed = $this->mediaService->preview($media, $size);

            if ($fileType == 'image') {
                $response = $compressed->response('jpeg', $size === 'raw' ? 100 : $size);
                $response->headers->set('Content-Disposition', sprintf('inline; filename="%s"', $mediaFileName));
                return $response;
            }

            return response()->file($compressed[0], $compressed[1]);
        }

        return response()->file($this->mediaService->preview($media));
    }

    public function getResourceSize($fileType, $size = null)
    {
        $sizes = [
            'image' => [
                'allowed_sizes' => ['small', 'medium', 'raw', 'default'],
                'sizes' => [
                    'small'     => 25,
                    'medium'    => 50,
                    'raw'       => 'raw',
                    'default'   => 90
                ],
                'error_message' => ''
            ],
            'video' => [
                'allowed_sizes' => ['lowest', 'very_low', 'low', 'standard', 'hd', 'raw', 'thumbnail', 'default'],
                'sizes' => [
                    'lowest'        => '144p',
                    'very_low'      => '240p',
                    'low'           => '360p',
                    'standard'      => '480p',
                    'hd'            => '720p',
                    'raw'           => 'raw',
                    'thumbnail'     => 'thumbnail',
                    'default'       => 'raw'
                ],
                'error_message' => ''
            ]
        ];

        if ($size === null) $size = 'default';

        foreach ($sizes as $k => $v) {
            $sizes[$k]['error_message'] = $this->setErrorMessage($sizes[$k]['allowed_sizes']);
        }

        if (!in_array($size, $sizes[$fileType]['allowed_sizes'])) {
            throw new Error($sizes[$fileType]['error_message']);
            $size = 'default';
        }

        return $sizes[$fileType]['sizes'][$size];
    }

    private function setErrorMessage($sizes)
    {
        $errorMessage = "Size parameter must be equals to ";

        for ($i = 0; $i < count($sizes); $i++) {
            $current = $sizes[$i];
            if ($i < count($sizes) - 2) {
                $errorMessage .= "'$current', ";
            } else if ($i < count($sizes) - 1) {
                $errorMessage .= "'$current' ";
            } else if ($i == count($sizes) - 1) {
                if ($i == 0) {
                    $errorMessage .= "'$current'.";
                } else {
                    $errorMessage .= "or '$current'.";
                }
            }
        }
        
        return $errorMessage;
    }

    /**
     * @param $damUrl
     * @param null $size
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($damUrl, $size = null)
    {
        $mediaId = DamUrlUtil::decodeUrl($damUrl);
        $media = Media::findOrFail($mediaId);
        $mimes = new MimeTypes;
        $fileName = $damUrl . "." . $mimes->getExtension($media->mime_type); // json
        $thumb = $this->getThumbnailBySize($size, $media);
        if ($thumb) {
            return response()->download($thumb, $fileName);
        }
        return response()->download($media->getPath(), $fileName);
    }

    /**
     * @return array
     */
    public function listTypes()
    {
        return ResourceType::getKeys();
    }

    /**
     * @param DamResource $damResource
     * @param addUseRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function addUse(DamResource $damResource, addUseRequest $request)
    {
        $resource = $this->resourceService->addUse($damResource, $request->all());
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param DamResource $damResource
     * @param DamResourceUse $damResourceUse
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function deleteUse(DamResource $damResource, DamResourceUse $damResourceUse)
    {
        $resource = $this->resourceService->deleteUse($damResource, $damResourceUse);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Method to delete a concrete media id associated with a damResource
     * @param DamResource $damResource
     * @param Media $media
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Exception
     */
    public function deleteAssociatedFile(DamResource $damResource, Media $media)
    {
        $resource = $this->resourceService->deleteAssociatedFile($damResource, $media);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Method to delete a array of media ids associated with a damResource
     * @param DamResource $damResource
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Exception
     */
    public function deleteAssociatedFiles(damResource $damResource, Request $request)
    {
        $idsToDelete = $request->all();
        if (!empty($idsToDelete)) {
            $resource = $this->resourceService->deleteAssociatedFiles($damResource, array_values($idsToDelete));
            return (new ResourceResource($resource))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } else {
            return response(['error' => 'need to send a array of media ids']);
        }
    }

    public function updateAsLastCreated(DamResource $damResource)
    {
        $resource = $this->resourceService->updateAsLast($damResource, true);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function updateAsLastUpdated(DamResource $damResource)
    {
        $resource = $this->resourceService->updateAsLast($damResource, false);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function updateAsOther(DamResource $damResource, DamResource $otherResource)
    {
        $resource = $this->resourceService->updateAsOther($damResource, $otherResource);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function renderCDNResource(CDNRequest $request)
    {
        $cdnInfo = $this->cdnService->getCDNInfo($request->cdn_code);
        $resource = $request->getAttachedDamResource();

        if ($cdnInfo === null)
            return response(['error' => 'This CDN doesn\'t exist!']);

        if (!$request->isCollectionAccessible($resource, $cdnInfo) || !$cdnInfo->checkAccessRequirements($_SERVER['REMOTE_ADDR']))
            return response(['error' => 'Forbidden access!']);

        if (!isset($request->size))
            $request->size = null;

        $resourceResponse = new ResourceResource($resource);
        $responseJson = json_decode($resourceResponse->toJson());
    
        if (count($responseJson->files) == 0)
            return response(['error' => 'No files attached!']);
        
        return $this->renderResource($responseJson->files[0]->dam_url, $request->size);
    }
}
