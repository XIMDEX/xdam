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
use App\Http\Requests\Workspace\SetResourceWorkspaceRequest;
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
use App\Services\UserService;
use App\Services\OrganizationWorkspace\WorkspaceService;
use App\Utils\DamUrlUtil;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Resources\Json\JsonResource;
use Mimey\MimeTypes;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use App\Enums\AccessPermission;
use App\Models\Copy;
use App\Services\ExternalApis\ScormService;
use App\Services\RenderService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

use Intervention\Image\Facades\Image;



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
     * @var WorkspaceService
     */
    private $workspaceService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var ScormService
     */
    private $scormService;

    /**
     * @var RenderService
     */
    private $renderService;

    /**
     * CategoryController constructor.
     * @param ResourceService $resourceService
     * @param MediaService $mediaService
     * @param CDNService $cdnService
     * @param WorkspaceService $workspaceService
     * @param UserService $userService
     * @param ScormService $scormService
     */
    public function __construct(
        ResourceService $resourceService,
        MediaService $mediaService,
        CDNService $cdnService,
        WorkspaceService $workspaceService,
        UserService $userService,
        ScormService $scormService,
        RenderService $renderService
    ) {
        $this->resourceService = $resourceService;
        $this->mediaService = $mediaService;
        $this->cdnService = $cdnService;
        $this->workspaceService = $workspaceService;
        $this->userService = $userService;
        $this->scormService = $scormService;
        $this->renderService = $renderService;
    }

    public function resourcesSchema()
    {
        $schemas = $this->resourceService->resourcesSchema();
        return response()->json($schemas);
    }

    public function lomesSchema()
    {
        return response()->json($this->resourceService->lomesSchema());
    }

    public function lomSchema()
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
                if ($thumbSize != '' && strpos($supportedThumbnail, $thumbSize) !== false) {
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
        return (new ExploreCoursesCollection($this->resourceService->exploreCourses($request->user_id)))
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
        $resource = $this->resourceService->update($damResource, $request->all(),$this->getAvailableResourceSizes());
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param string $damResource
     * @param UpdateResourceRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function updateFromXeval(string $xevalId, UpdateResourceRequest $request)
    {
        $damResource = $resource = DamResource::whereJsonContains('data->description', ['xeval_id' => $xevalId])->first();
        $resource = $this->resourceService->updateFromXeval($damResource, $request->all(),$this->getAvailableResourceSizes());
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
        $resource = $this->resourceService->store( params: $request->all(),
        availableSizes: $this->getAvailableResourceSizes());
        return response(new ResourceResource($resource))
            ->setStatusCode(Response::HTTP_OK);
    }

    public function duplicate(DamResource $damResource)
    {
        $duplicated = false;
        try {
            $duplicatedResource =  $this->resourceService->duplicateResource($damResource);
            $this->resourceService->processDuplicateExtraData($duplicatedResource, $this->resourceService->getLomData($damResource), "lom");
            $this->resourceService->processDuplicateExtraData($duplicatedResource, $this->resourceService->getLomesData($damResource), "lomes");
            $duplicated = true;
            $this->scormService->cloneBook($duplicatedResource->id);
        } catch (\Exception $exc) {
            $this->resourceService->duplicateUpdateStatus($duplicatedResource->id, ['message' => $exc->getMessage(), 'status' => 'error']);
            $message = $exc->getMessage();
            if ($duplicated) {
                $message = 'Book cloned in XDAM but with errors: ' . $message;
            }
            return response()->json([
                'error' => $message
            ])->setStatusCode($duplicated ? Response::HTTP_OK : Response::HTTP_BAD_GATEWAY);
        }

        return response(new ResourceResource($duplicatedResource))
            ->setStatusCode(Response::HTTP_OK);
    }

    public function copyStatus($copy, Request $request)
    {
        $resource = $this->resourceService->duplicateUpdateStatus($copy, $request->all());
        return response(new JsonResource($resource))
            ->setStatusCode(Response::HTTP_OK);
    }

    public function copyGetStatus($copy)
    {
        $resource = $this->resourceService->getCopy($copy);
        return response(new JsonResource($resource))
            ->setStatusCode(Response::HTTP_OK);
    }

    public function storeBatch(Request $request)
    {
        $resources = $this->resourceService->storeBatch( data: $request->all(),
        availableSizes: $this->getAvailableResourceSizes());
        return (new ResourceCollection($resources))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function setLomesData(DamResource $damResource,  Request $request)
    {
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

    public function getLomesData(DamResource $damResource)
    {
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
    public function delete($damResource)
    {
        $res = $this->resourceService->delete($damResource);
        return response(['deleted' => $res, 'resource' => $damResource], Response::HTTP_OK);
    }

    /**
     * @param DamResource $damResource
     * @return \Illuminate\Http\Response
     */
    public function softDelete(Request $request, DamResource $damResource)
    {
        $res = $this->resourceService->softDelete($damResource, $request->boolean('force'), $request->boolean('only_local'));
        return response(['soft_deleted' => $res, 'resource' => $damResource], Response::HTTP_OK);
    }

    /**
     * @param $damResourceId -> can't use DamResource class as it represents a soft deleted resource
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, $damResourceId)
    {
        $res = $this->resourceService->restore($damResourceId, $request->boolean('only_local'));
        return response(['restored' => $res, 'resource_id' => $damResourceId], Response::HTTP_OK);
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
        $resource = $is_course ? $this->resourceService->setOnlyOneCategoryTo($damResource, $category) : $this->resourceService->addCategoryTo($damResource, $category);
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
    public function render(Request $request, $damUrl, $size = 'default')
    {
        $mediaId = DamUrlUtil::decodeUrl($damUrl);
        //if (Cache::has("{$mediaId}__{$size}")) {
        //    $response = Cache::get("{$mediaId}__$size");
        //    if (is_string($response)) {
        //        Cache::delete("{$mediaId}__$size");
        //    }
        //}
        $method = request()->method();
        return $this->renderResource($request, $mediaId, $method, $size, null, false);
    }

    private function renderResource(Request $request, $mediaId, $method = null, $size = null, $renderKey = null, $isCDN = false, $can_download = false)
    {
        $media = Media::findOrFail($mediaId);
        $absolutePath = $media->getPath();
        $mediaFileName = pathinfo($absolutePath, PATHINFO_FILENAME);
        $path = explode(Storage::path(''), $absolutePath)[1];

        $originalSize = Storage::size($path); //file weight
        $size = ($size === null ? 'default' : $size); //image variant
        $mimeType = $media->mime_type;
        $fileType = explode('/', $mimeType)[0];
        $response = null;

        if ($fileType == 'image' && $size === 'default') {

            if ($this->renderService->checkAvif($request)) { // does the browser allow AVIF ?
                $avifPath = $this->renderService->getConvertedPath($path, "avif");
                if (!$this->renderService->checkIfImageExists($avifPath)) {
                    $this->renderService->generateAvif($path);
                    $avifSize = Storage::size($avifPath);
                    //$this->renderService->logAvifConversion($mediaFileName, $originalSize, $avifSize);
                }
                //$file = Storage::get($avifPath);
                $fileVariantPath = $avifPath;
                $type = 'image/avif';
            } else {
                // if it weights under 2 Mbytes returns original and do not create variant
                if ($originalSize < 2000000) {
                    //$file = Storage::get($path);
                    $fileVariantPath = $path;
                    $type = $mimeType;
                } else {
                    list($fileVariantPath, $type) = $this->handleSizedImageRendering($path, $media, $fileType, 'medium', $mimeType);
                }
            }

            $lastModified = Storage::lastModified($fileVariantPath);
            $streamedResponse = $this->createStreamedResponse($fileVariantPath, $type, $mediaFileName);
            //$cachedResponse = $this->createCachedResponse($file, $streamedResponse);
            //Cache::put("{$mediaId}__$size", $cachedResponse);
            $response = $streamedResponse;

        } else if ($fileType == 'image' && $size === 'raw') {

            //$file = Storage::get($path);
            $lastModified = Storage::lastModified($path);
            $streamedResponse = $this->createStreamedResponse($path, $mimeType, $mediaFileName);
            //$cachedResponse = $this->createCachedResponse($file, $streamedResponse);
            //Cache::put("{$mediaId}__$size", $cachedResponse);
            $response = $streamedResponse;

	}  else if ($fileType == 'image' && $size) {

            list($fileVariantPath, $type) = $this->handleSizedImageRendering($path, $media, $fileType, $size, $mimeType);
            $lastModified = Storage::lastModified($path);
            $streamedResponse = $this->createStreamedResponse($fileVariantPath, $type, $mediaFileName);
            //$cachedResponse = $this->createCachedResponse($file, $streamedResponse);
            $response = $streamedResponse;

	} else if ($fileType == 'video') {

            $sizeValue = $this->getResourceSize($fileType, $size);
            $availableSizes = $this->getAvailableResourceSizes();

            $compressed = $this->mediaService->preview($media, $availableSizes[$fileType], $size, $sizeValue);

            if (in_array($size, ['medium', 'small', 'thumbnail'])) {
                $response = $this->handleImageAndVideoResponse($fileType, $size, $compressed, $mediaFileName, $mediaId, $availableSizes);
            }else{
                $response = response()->file($compressed);
            }

        } else if ($mimeType == 'application/pdf' && $renderKey == null && $isCDN) {
            $route = Route::getCurrentRoute();
            $routeParams = $route->parameters();
            $routeName = $route->getName();
            $url = route($routeName, $routeParams);
            $key = $this->mediaService->generateRenderKey();

            return view('pdfViewer', [
                'title' => $mediaFileName,
                'url'   => base64_encode($url . '?key=' . $key . '&dx=' . ($can_download ? 1 : 0))
            ]);
        } else if ($mimeType == 'application/pdf' && $renderKey != null && $isCDN) {

            if ($this->mediaService->checkRendererKey($renderKey, $method)) {
                // file lo hace con streaming de datos
                // $response = response()->file($this->mediaService->preview($media, []));
                // download lo hace sin streaming de datos
                $response = response()->download($this->mediaService->preview($media, []));
                $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            } else {
                return response(['error' => 'Error! You don\'t have permission to view this file.'], Response::HTTP_BAD_REQUEST);
            }
	}
        if ($fileType !== 'audio' && !$can_download && !$response ) {
            return response(['error' => 'Error! You don\'t have permission to download this file.'], Response::HTTP_BAD_REQUEST);
        }
        if (!$response) {
            return response()->file($this->mediaService->preview($media, []));
        }
        return $response;

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

    private function getResourceSize($fileType, $size = null)
    {
        $sizes = $this->getAvailableResourceSizes();

        if ($size === null) $size = 'default';

        if (!in_array($size, $sizes[$fileType]['allowed_sizes'])) {
            throw new Error($sizes[$fileType]['error_message']);
            $size = 'default';
        }

        return $sizes[$fileType]['sizes'][$size];
    }

    /**
     * @param $damUrl
     * @param null $size
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($damUrl, $size = null)
    {
       //JAP REVIEW CANDOWNLOAD FOR IMAGES
        $mediaId = DamUrlUtil::decodeUrl($damUrl);
        $media = Media::findOrFail($mediaId);

        $mimes = new MimeTypes;
        $fileName = $damUrl . "." . $mimes->getExtension($media->mime_type); // json
        $mediaFileName = $fileName;
        $size = ($size === null ? 'default' : $size);
        //JAP return raw file
        if ($size == 'default') $size = 'raw';

        $mimeType = $media->mime_type;
        $fileType = explode('/', $mimeType)[0];

        if ($size === 'raw' && $fileType === 'image') {
            return response()->download($media->getPath(), $fileName);
        } else if ($fileType == 'video' || $fileType == 'image') {
            $sizeValue = $this->getResourceSize($fileType, $size);
            $availableSizes = $this->getAvailableResourceSizes();
            $compressed = $this->mediaService->preview($media, $availableSizes[$fileType], $size, $sizeValue, true);

            if ($fileType == 'image' || ($fileType == 'video' && in_array($size, ['medium', 'small', 'thumbnail']))) {
                //JAP: revisar
                $response = $compressed->response('jpeg', $availableSizes[$fileType]['sizes'][$size] === 'raw' ? 100 : $availableSizes[$fileType]['qualities'][$size]);
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $mediaFileName));
                $response->header('Cache-Control', 'public, max-age=86400'); // Configura el tiempo de caché en segundos (en este caso, 24 horas)
                $response->header('Expires', gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT'); // Configura la fecha de expiración
                return $response;
            }

            return response()->download($compressed->getPath(), null, ['Content-Disposition' => sprintf('attachment; filename="%s"', $mediaFileName)]);
        }

        /*$thumb = $this->getThumbnailBySize($size, $media);
        if ($thumb) {
            return response()->download($thumb, $fileName);
        }*/
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

    private function getFileType($damUrl)
    {
        $mediaId = DamUrlUtil::decodeUrl($damUrl);
        $media = Media::findOrFail($mediaId);
        $mediaFileName = explode('/', $media->getPath());
        $mediaFileName = $mediaFileName[count($mediaFileName) - 1];
        $mimeType = $media->mime_type;
        return $mimeType;
    }

    public function renderCDNResourceFile(CDNRequest $request)
    {
        $method = request()->method();
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $originURL = $request->headers->get('referer');

        $data = $this->cdnService->decodeHash($request->damResourceHash);
        $damResourceHash = $data['damResourceHash'];

        $resource = $this->cdnService->getAttachedDamResource($damResourceHash);

        if ($resource === null)
            return response(['error' => 'Error! No resource found.'], Response::HTTP_BAD_REQUEST);

        $cdnInfo = $this->cdnService->getCDNAttachedToDamResource($damResourceHash, $resource);

        if ($cdnInfo === null)
            return response(['error' => 'This CDN doesn\'t exist!'], Response::HTTP_BAD_REQUEST);

        $accessCheck = false;
        $resourceResponse = new ResourceResource($resource);
        $responseJson = json_decode($resourceResponse->toJson());

        if (isset($request->size) && $this->getFileType($responseJson->files[0]->dam_url) === 'application/pdf')
            $accessCheck = true;

        $checkData = [
            'ipAddress' => $ipAddress,
            'originURL' => $originURL
        ];

        if ($this->cdnService->hasAccessPersmission(AccessPermission::workspace, $cdnInfo->id)) {
            $extra_data = [
                'data_token' => $data,
                'data_resource' => [
                    'workspaces' => $resource->workspaces()->get(),
                    'categories' => $resource->categories()->get()
                ]
            ];
            $checkData = array_merge($checkData, $extra_data);
        }

        if ($cdnInfo->checkAccessRequirements($checkData))
            $accessCheck = true;

        if (!$accessCheck)
            return response()->json(['error' => 'You can\'t access this CDN.'], Response::HTTP_UNAUTHORIZED);

        if (!$this->cdnService->isCollectionAccessible($resource, $cdnInfo))
            return response(['error' => 'Forbidden access!'], Response::HTTP_BAD_REQUEST);

        if (!isset($request->size))
            $request->size = null;

        if (count($responseJson->files) == 0)
            return response(['error' => 'No files attached!']);


        $mediaId = DamUrlUtil::decodeUrl($responseJson->files[0]->dam_url);
        $size = $request->size;
        //if (Cache::has("{$mediaId}__{$size}")) {
        //    return Cache::get("{$mediaId}__$size");
        //}

        $can_download = $resource->type == ResourceType::document ? ($resource->data->description->can_download ?? false) : true;
        return $this->renderResource($request, $mediaId, $method, $size, $request->key, true, $can_download);
    }

    public function renderCDNResource(CDNRequest $request)
    {
        $method = request()->method();
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $originURL = $request->headers->get('referer');


        $data = $this->cdnService->decodeHash($request->damResourceHash);
        $damResourceHash = $data['damResourceHash'];
        $resource = $this->cdnService->getAttachedDamResource($damResourceHash);

        if ($resource === null) {
            return response(['error' => 'Error! No resource found.'], Response::HTTP_BAD_REQUEST);
        }

        $cdnInfo = $this->cdnService->getCDNAttachedToDamResource($damResourceHash, $resource);
        if ($cdnInfo === null) {
            return response(['error' => 'This CDN doesn\'t exist!'], Response::HTTP_BAD_REQUEST);
        }

        $accessCheck = $this->checkAccess($request, $resource, $cdnInfo, $ipAddress, $originURL);
        if (!$accessCheck) {
            return response()->json(['error' => 'You can\'t access this CDN.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->cdnService->isCollectionAccessible($resource, $cdnInfo)) {
            return response(['error' => 'Forbidden access!'], Response::HTTP_BAD_REQUEST);
        }

        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }


    public function setWorkspace(SetResourceWorkspaceRequest $request)
    {
        if (!$request->checkResourceWorkspaceChangeData())
            return response(['error' => 'The needed data hasn\'t been provided.']);

        $user = $this->userService->user();

        if ($user === null)
            return response(['error' => 'User inaccessible.']);

        $resource = DamResource::where('id', $request->damResource)
            ->first();

        if ($resource === null)
            return response(['error' => 'Resource doesn\'t exist.']);

        $result = $this->workspaceService->setResourceWorkspace($user, $resource, $request->workspaces);
        return response($result)->setStatusCode(Response::HTTP_OK);
    }

    public function getMaxFiles(DamResource $damResource)
    {
        $collection = $damResource->collection;
        if ($collection === null) return response(['error' => 'No collection info found.'], Response::HTTP_BAD_REQUEST);
        return response(['max_files' => $collection->max_number_of_files], Response::HTTP_OK);
    }

    public function getFilesCount(DamResource $damResource)
    {
        return response(['files_count' => $damResource->getNumberOfFilesAttached()], Response::HTTP_OK);
    }

    public function checkAccess($request, $resource, $cdnInfo, $ipAddress, $originURL)
    {
        $resourceResponse = new ResourceResource($resource);
        $responseJson = json_decode($resourceResponse->toJson());

        if (isset($request->size) && $this->getFileType($responseJson->files[0]->dam_url) === 'application/pdf') {
            return true;
        }

        $checkData = [
            'ipAddress' => $ipAddress,
            'originURL' => $originURL
        ];

        if ($this->cdnService->hasAccessPersmission(AccessPermission::workspace, $cdnInfo->id)) {
            $data = $this->cdnService->decodeHash($request->damResourceHash);
            $extra_data = [
                'data_token' => $data,
                'data_resource' => [
                    'workspaces' => $resource->workspaces()->get(),
                    'categories' => $resource->categories()->get()
                ]
            ];
            $checkData = array_merge($checkData, $extra_data);
        }

        if ($cdnInfo->checkAccessRequirements($checkData)) {
            return true;
        }

        return false;
    }

    public function retryClone($copy)
    {
        try {
            $this->scormService->cloneBook($copy);
        } catch (\Exception $exc) {
            $this->resourceService->duplicateUpdateStatus($copy, ['message' => $exc->getMessage(), 'status' => 'error']);
            $message = $exc->getMessage();

            return response()->json([
                'error' => $message
            ])->setStatusCode(Response::HTTP_BAD_GATEWAY);
        }
    }

    private function handleSizedImageRendering(string $path, Media $media, string $fileType, string $size, string $mimeType): array
    {
        $pathSize = $this->renderService->appendSizeToPath($path, $size);

        if (!Storage::exists($path)) {
            abort(404);
        }

        if (!Storage::exists($pathSize)) {
            $sizeValue = $this->getResourceSize($fileType, $size);
            $availableSizes = $this->getAvailableResourceSizes();
            $compressed = $this->mediaService->preview($media, $availableSizes[$fileType], $size, $sizeValue);
        }

        return [$pathSize, $mimeType];
    }

    private function createStreamedResponse(string $fileVariantpath, string $type, string $mediaFileName): StreamedResponse
    {
        $response = new StreamedResponse();
        $file = Storage::get($fileVariantpath);
        $response->setCallback(function () use ($file) {
            echo $file;
        });

        $maxAge = 3600 * 24 * 7;

        $response->headers->set('Content-Type', $type);
        $response->headers->set('Content-Length', strlen($file));
        $response->headers->set('Content-Disposition', sprintf('inline; filename="%s"', $mediaFileName));
        $response->headers->set('Cache-Control', 'public, max-age=' . $maxAge . ', immutable');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');

        $etag = md5($file);
        $response->setEtag($etag);

        $lastModified = Storage::lastModified($fileVariantpath);
        $response->setLastModified(Carbon::createFromTimestamp($lastModified));

        return $response;
    }

    private function createCachedResponse(string $file, StreamedResponse $streamedResponse): Response
    {
        return new Response(
            $file,
            Response::HTTP_OK,
            $streamedResponse->headers->all()
        );
    }

    private function handleImageAndVideoResponse($fileType, $size, $compressed, $mediaFileName, $mediaId, $availableSizes)
    {
        if ($fileType == 'image' || ($fileType == 'video' && in_array($size, ['medium', 'small', 'thumbnail']))) {
            $response = response()->file($compressed->origin()->filePath());
            $this->setCommonHeaders($response, $mediaFileName, $compressed);

           // if ($fileType == 'image') {
           //     $response_cache = $this->createImageCacheResponse($compressed, $fileType, $size, $availableSizes, $mediaFileName);
           //     Cache::put("{$mediaId}__$size", $response_cache);
           // }

            return $response;
        }

        return null;
    }

    private function setCommonHeaders($response, $mediaFileName, $compressed)
    {
        $maxAge = 3600 * 24 * 7;
        $response->headers->set('Content-Disposition', sprintf('inline; filename="%s"', $mediaFileName));
        $response->headers->set('Cache-Control', 'public, max-age=' . $maxAge . ', immutable');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');

        $etag = md5_file($compressed->origin()->filePath());
        $response->setEtag($etag);

        $lastModified = filemtime($compressed->origin()->filePath());
        $response->setLastModified(Carbon::createFromTimestamp($lastModified));
    }

    //JAP VERIFICAR uso funcion
    private function createImageCacheResponse($compressed, $fileType, $size, $availableSizes, $mediaFileName)
    {
        $quality = $availableSizes[$fileType]['sizes'][$size] === 'raw' ? 100 : $availableSizes[$fileType]['qualities'][$size];
        $response_cache = $compressed->response('jpeg', $quality);

        $this->setCommonHeaders($response_cache, $mediaFileName, $compressed);

        return $response_cache;
    }
}
