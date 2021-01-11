<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Models\Category;
use App\Models\DamResource;
use App\Models\DamResourceUse;
use App\Utils\DamUrlUtil;
use Exception;
use Illuminate\Support\Collection;
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
     * @param SolrService $solr
     */
    public function __construct(MediaService $mediaService, SolrService $solr)
    {
        $this->mediaService = $mediaService;
        $this->solr = $solr;
    }

    /**
     * @param DamResource $resource
     * @return stdClass
     */
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
        foreach ($previews as $preview) {
            $parent_id = $preview->hasCustomProperty('parent_id') ? $preview->getCustomProperty('parent_id') : "";
            $class->previews[] = DamUrlUtil::generateDamUrl($preview, $parent_id);
        }
        $files = $this->mediaService->list($resource, MediaType::File()->key, true);
        foreach ($files as $file) {
            $parent_id = $file->hasCustomProperty('parent_id') ? $file->getCustomProperty('parent_id') : "";
            $class->files[] = DamUrlUtil::generateDamUrl($file, $parent_id);
        }
        return $class;
    }

    /**
     * @param $model
     * @param $params
     */
    private function saveAssociatedFiles($model, $params)
    {
        if (array_key_exists(MediaType::File()->key, $params) && $params[MediaType::File()->key]) {
            $this->mediaService->addFromRequest($model, null, MediaType::File()->key, ["parent_id" => $model->id], $params[MediaType::File()->key]);
        }

        if (array_key_exists(MediaType::Preview()->key, $params) && $params[MediaType::Preview()->key]) {
            $this->mediaService->addFromRequest($model, null, MediaType::Preview()->key, ["parent_id" => $model->id], $params[MediaType::Preview()->key]);
        }
    }

    /**
     * @param $resource
     * @param $data
     * @param $type
     * @return null
     * @throws Exception
     */
    private function linkCategoriesFromJson($resource, $data, $type)
    {
        if (property_exists($data, "description")) {
            if (property_exists($data->description, "category")) {
                $category = Category::where("type", "=", $type)->where("name", $data->description->category)->first();
                if (null != $category) {
                    $this->deleteCategoryFrom($resource, $category);
                    $this->addCategoryTo($resource, $category);
                }
            }
        }

        return null;
    }

    /**
     * @return Collection
     */
    public function getAll()
    {
        return DamResource::all();
    }

    /**
     * @param DamResource $resource
     * @return DamResource
     */
    public function get(DamResource $resource): DamResource
    {
        return $resource;
    }

    /**
     * @return mixed
     */
    public function exploreCourses()
    {
        return Category::where('type', ResourceType::course)->get();
    }

    /**
     * @param DamResource $resource
     * @param $params
     * @return DamResource
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function update(DamResource $resource, $params)
    {
        if (array_key_exists("type", $params) && $params["type"]) {
            $updated = $resource->update([
                'type' => ResourceType::fromKey($params["type"])->value,
            ]);
        }

        if (array_key_exists("data", $params) && $params["data"]) {
            $updated = $resource->update([
                'data' => $params['data'],
            ]);
            $this->linkCategoriesFromJson($resource, json_decode($params["data"]), $resource->type);
        }
        $this->saveAssociatedFiles($resource, $params);
        $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($resource));
        $resource->refresh();
        return $resource;
    }

    /**
     * @param $params
     * @return DamResource
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function store($params): DamResource
    {
        $name = array_key_exists('name', $params) ? $params["name"] : "";
        if (empty($name) && array_key_exists(MediaType::File()->key, $params)) {
            $name = $params[MediaType::File()->key]->getClientOriginalName();
        }
        $type = ResourceType::fromKey($params["type"])->value;
        $newResource = DamResource::create([
            'data' => $params['data'],
            'name' => $name,
            'type' => $type,
        ]);

        $this->linkCategoriesFromJson($newResource, json_decode($params["data"]), $type);
        $this->saveAssociatedFiles($newResource, $params);
        $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($newResource));
        $newResource->refresh();
        return $newResource;
    }

    /**
     * @param DamResource $resource
     * @throws Exception
     */
    public function delete(DamResource $resource)
    {
        $this->solr->deleteDocumentById($resource->id);
        $resource->delete();
    }

    /**
     * @param DamResource $resource
     * @param $requestKey
     * @return DamResource
     */
    public function addPreview(DamResource $resource, $requestKey)
    {
        $this->mediaService->addFromRequest($resource, $requestKey, MediaType::Preview()->key, ["parent_id" => $resource->id]);
        $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($resource));
        return $resource;
    }

    /**
     * @param DamResource $resource
     * @param $requestKey
     * @return DamResource
     */
    public function addFile(DamResource $resource, $requestKey)
    {
        $this->mediaService->addFromRequest($resource, $requestKey, MediaType::File()->key, ["parent_id" => $resource->id]);
        return $resource;
    }

    /**
     * @param DamResource $resource
     * @param Category $category
     * @return DamResource
     * @throws Exception
     */
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

    /**
     * @param DamResource $resource
     * @param $requestKey
     * @return DamResource
     */
    public function setTags(DamResource $resource, $tags = [])
    {
        $resource->setTags($tags);
        return $resource;
    }

    /**
     * @param DamResource $resource
     * @param Category $category
     * @return DamResource
     * @throws Exception
     */
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

    /**
     * @param DamResource $resource
     * @param $params
     * @return DamResource
     */
    public function addUse(DamResource $resource, $params)
    {
        $resource->uses()->create($params);
        return $resource;
    }

    /**
     * @param DamResource $resource
     * @param DamResourceUse $damResourceUse
     * @return DamResource
     * @throws Exception
     */
    public function deleteUse(DamResource $resource, DamResourceUse $damResourceUse)
    {
        $resource->uses()->findOrFail($damResourceUse->id)->delete();
        return $resource;
    }
}
