<?php

namespace App\Http\Controllers;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Enums\ThumbnailTypes;
use App\Http\Requests\addFileToResourceRequest;
use App\Http\Requests\addPreviewToResourceRequest;
use App\Http\Requests\addUseRequest;
use App\Http\Requests\deleteUseRequest;
use App\Http\Requests\DownloadMultipleRequest;
use App\Http\Requests\GetDamResourceRequest;
use App\Http\Requests\ResouceCategoriesRequest;
use App\Http\Requests\SetTagsRequest;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Http\Resources\ExploreCoursesCollection;
use App\Http\Resources\ResourceCollection;
use App\Http\Resources\ResourceResource;
use App\Models\Category;
use App\Models\DamResource;
use App\Models\DamResourceUse;
use App\Models\Media;
use App\Services\MediaService;
use App\Services\ResourceService;
use App\Utils\DamUrlUtil;
use App\Utils\FileUtil;
use Illuminate\Support\Facades\Request;
use Mimey\MimeTypes;
use Symfony\Component\HttpFoundation\Response;

class ResourceController extends Controller
{
    private $resourceService;
    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * CategoryController constructor.
     * @param ResourceService $resourceService
     * @param MediaService $mediaService
     */
    public function __construct(ResourceService $resourceService, MediaService $mediaService)
    {
        $this->resourceService = $resourceService;
        $this->mediaService = $mediaService;
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
    public function getAll()
    {
        $resources = $this->resourceService->getAll();
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

    /**
     * @param DamResource $damResource
     * @return \Illuminate\Http\Response
     */
    public function delete(DamResource $damResource)
    {
        $this->resourceService->delete($damResource);
        return response(null, Response::HTTP_NO_CONTENT);
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
        $resource = $this->resourceService->addCategoryTo($damResource, $category);
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
        $mediaId = DamUrlUtil::decodeUrl($damUrl);
        $media = Media::findOrFail($mediaId);
        $thumb = $this->getThumbnailBySize($size, $media);
        if ($thumb) {
            return response()->file($thumb);
        }
        return response()->file($this->mediaService->preview(Media::findOrFail($mediaId)));
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
}
