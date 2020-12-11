<?php

namespace App\Http\Controllers;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Http\Requests\addFileToResourceRequest;
use App\Http\Requests\addPreviewToResourceRequest;
use App\Http\Requests\addUseRequest;
use App\Http\Requests\deleteUseRequest;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Http\Resources\DamResourceUseResource;
use App\Http\Resources\ResourceCollection;
use App\Http\Resources\ResourceResource;
use App\Models\Category;
use App\Models\DamResource;
use App\Models\DamResourceUse;
use App\Models\Media;
use App\Services\MediaService;
use App\Services\ResourceService;
use App\Utils\DamUrlUtil;
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
     */
    public function __construct(ResourceService $resourceService, MediaService $mediaService)
    {
        $this->resourceService = $resourceService;
        $this->mediaService = $mediaService;
    }

    public function getAll()
    {
        $resources =  $this->resourceService->getAll();
        return (new ResourceCollection($resources))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function get(DamResource $damResource)
    {
        $damResource =  $this->resourceService->get($damResource);
        return (new ResourceResource($damResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function update(DamResource $damResource, UpdateResourceRequest $request)
    {
        $resource =  $this->resourceService->update($damResource, $request->all());
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function store(StoreResourceRequest $request)
    {
        $resource = $this->resourceService->store($request->all());
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function delete(DamResource $damResource)
    {
        $this->resourceService->delete($damResource);
        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function addPreview(DamResource $damResource, addPreviewToResourceRequest $request)
    {
        $resource =  $this->resourceService->addPreview($damResource, MediaType::Preview()->key);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function addFile(DamResource $damResource, addFileToResourceRequest $request)
    {
        $resource =  $this->resourceService->addFile($damResource, MediaType::File()->key);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function addCategory(DamResource $damResource, Category $category)
    {
        $resource =  $this->resourceService->addCategoryTo($damResource, $category);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function deleteCategory(DamResource $damResource, Category $category)
    {
        $resource =  $this->resourceService->deleteCategoryFrom($damResource, $category);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function render($damUrl)
    {
        $mediaId = DamUrlUtil::decodeUrl($damUrl);
        return response()->file($this->mediaService->preview(Media::findOrFail($mediaId)));
    }

    public function listTypes()
    {
        return ResourceType::getKeys();
    }

    public function addUse(DamResource $damResource, addUseRequest $request)
    {
        $resource =  $this->resourceService->addUse($damResource, $request->all());
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }


    public function deleteUse(DamResource $damResource, DamResourceUse $damResourceUse)
    {
        $resource =  $this->resourceService->deleteUse($damResource, $damResourceUse);
        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

}
