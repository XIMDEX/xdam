<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Http\Resources\DamResourceUseResource;
use App\Http\Resources\MediaResource;
use App\Models\Category;
use App\Models\DamResource;
use App\Models\DamResourceUse;
use App\Utils\DamUrlUtil;
use Exception;
use stdClass;

class ResourceService
{
    /**
     * @var MediaService
     */
    private $mediaService;
    /**
     * @var SolrService
     */
    private $solr;

    /**
     * ResourceService constructor.
     * @param MediaService $mediaService
     */
    public function __construct(MediaService $mediaService, SolrService $solr)
    {
        $this->mediaService = $mediaService;
        $this->solr = $solr;
    }

    private function prepareResourceToBeIndexed(DamResource $resource)
    {
        $class = new stdClass();
        $class->id = is_object($resource->id) ? $resource->id->toString() : $resource->id;
        $class->data = $resource->data;
        $class->name = $resource->name ?? '';
        $class->active = true;
        $class->type = ResourceType::fromValue($resource->type)->key;
        $class->categories = $resource->categories()->pluck('name')->toArray() ?? [""];
        $previews = $this->mediaService->list($resource, MediaType::Preview()->key, true);
        foreach ($previews as $preview)
        {
            $parent_id = $preview->hasCustomProperty('parent_id') ? $preview->getCustomProperty('parent_id') : "";
            $class->previews[] = DamUrlUtil::generateDamUrl($preview, $parent_id);
        }
        $files = $this->mediaService->list($resource, MediaType::File()->key, true);
        foreach ($files as $file)
        {
            $parent_id = $file->hasCustomProperty('parent_id') ? $file->getCustomProperty('parent_id') : "";
            $class->files[] = DamUrlUtil::generateDamUrl($file, $parent_id);
        }
        return $class;
    }

    public function getAll()
    {
        return DamResource::all();
    }

    public function get(DamResource $resource): DamResource
    {
        return $resource;
    }

    public function update(DamResource $resource, $params)
    {
        $updated = $resource->update([
            'data' => $params['data'],
            'type' => ResourceType::fromKey($params["type"])->value,
        ]);

        if (array_key_exists("file", $params) && $params["file"]) {
            $this->mediaService->addFromRequest($resource, "file", ["parent_id" => $resource->id]);
        }

        if (array_key_exists("preview", $params) && $params["preview"]) {
            $this->mediaService->addFromRequest($resource, "preview", ["parent_id" => $resource->id]);
        }

        $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($resource));

        if ($updated) {
            return $resource;
        }
        return false;
    }

    public function store($params): DamResource
    {
        $name = array_key_exists('name', $params) ? $params["name"] : "";
        if (empty($name) && array_key_exists(MediaType::File()->key, $params)) {
            $name = $params[MediaType::File()->key]->getClientOriginalName();
        }
        $newResource = DamResource::create([
            'data' => $params['data'],
            'name' => $name,
            'type' => ResourceType::fromKey($params["type"])->value,
        ]);

        if (array_key_exists(MediaType::File()->key, $params) && $params[MediaType::File()->key]) {
            $this->mediaService->addFromRequest($newResource, MediaType::File()->key, MediaType::File()->key, ["parent_id" => $newResource->id]);
        }

        if (array_key_exists(MediaType::Preview()->key, $params) && $params[MediaType::Preview()->key]) {
            $this->mediaService->addFromRequest($newResource, MediaType::Preview()->key, MediaType::File()->key, ["parent_id" => $newResource->id]);
        }

        $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($newResource));

        return $newResource;
    }

    public function delete(DamResource $resource)
    {
        $this->solr->deleteDocumentById($resource->id);
        $resource->delete();
    }

    public function addPreview(DamResource $resource, $requestKey)
    {
        $this->mediaService->addFromRequest($resource, $requestKey, MediaType::Preview()->key, ["parent_id" => $resource->id]);
        $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($resource));
        return $resource;
    }

    public function addFile(DamResource $resource, $requestKey)
    {
        $this->mediaService->addFromRequest($resource, $requestKey, MediaType::File()->key, ["parent_id" => $resource->id]);
        return $resource;
    }

    public function addCategoryTo(DamResource $resource, Category $category)
    {
        if ($category->type == $resource->type) {
            if (!$resource->hasCategory($category)) {
                $resource->categories()->attach($category);
            }
        } else {
            throw new Exception ("category type and resource type are not equals");
        }
        $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($resource));
        return $resource;
    }

    public function deleteCategoryFrom(DamResource $resource, Category $category)
    {
        if ($category->type == $resource->type) {
            if ($resource->hasCategory($category)) {
                $resource->categories()->detach($category);
                $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($resource));
            }
        } else {
            throw new Exception ("category type and resource type are not equals");
        }
        return $resource;
    }

    public function addUse(DamResource $resource, $params)
    {
        $resource->uses()->create($params);
        return $resource;
    }

    public function deleteUse(DamResource $resource, DamResourceUse $damResourceUse)
    {
        $resource->uses()->findOrFail($damResourceUse->id)->delete();
        return $resource;
    }
}
