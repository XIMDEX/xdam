<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Models\Category;
use App\Models\DamResource;
use App\Models\DamResourceUse;
use App\Services\Solr\SolrService;
use App\Utils\DamUrlUtil;
use Exception;
use Illuminate\Support\Collection;
use stdClass;

class ResourceService
{
    /**
     * @var MediaService
     */
    private MediaService $mediaService;
    /**
     * @var SolrService
     */
    private SolrService $solr;

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
    public function prepareResourceToBeIndexed(DamResource $resource): stdClass
    {
        $class = new stdClass();
        $class->id = is_object($resource->id) ? $resource->id->toString() : $resource->id;
        $class->data = $resource->data;
        $class->name = $resource->name ?? '';
        $class->active = true;
        $class->type = ResourceType::fromValue($resource->type)->key;
        $class->collection = $this->solr->getCollectionBySubType($class->type);
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
    private function saveAssociatedFiles($model, $params): void
    {
        if (array_key_exists(MediaType::File()->key, $params) && $params[MediaType::File()->key]) {
            $this->mediaService->addFromRequest(
                $model,
                null,
                MediaType::File()->key,
                ["parent_id" => $model->id],
                $params[MediaType::File()->key]
            );
        } else {
            $model->clearMediaCollection(MediaType::File()->key);
        }

        if (array_key_exists(MediaType::Preview()->key, $params) && $params[MediaType::Preview()->key]) {
            $this->mediaService->addFromRequest(
                $model,
                null,
                MediaType::Preview()->key,
                ["parent_id" => $model->id],
                $params[MediaType::Preview()->key]
            );
        } else {
            $model->clearMediaCollection(MediaType::Preview()->key);
        }
    }

    /**
     * @param $resource
     * @param $data
     * @param $type
     * @return null
     * @throws Exception
     */
    private function linkCategoriesFromJson($resource, $data): void
    {
        // define the possible names to associate a category inside a json
        $possibleKeyNameInJson = ["category", "categories"];
        if ($data && property_exists($data, "description")) {
            foreach ($possibleKeyNameInJson as $possibleKeyName) {
                // for each one we iterate, if there is a corresponding key,
                // we associate either a list of categories or a specific category to each resource
                if (property_exists($data->description, $possibleKeyName)) {
                    $property = $data->description->$possibleKeyName;
                    if (is_array($property)) {
                        foreach ($property as $child) {
                            $this->setCategories($resource, $child);
                        }
                    } else {
                        $this->setCategories($resource, $property);
                    }
                }
            }
        }
    }


    /**
     * @param $resource
     * @param $data
     * @return null
     * @throws Exception
     */
    private function linkTagsFromJson($resource, $data): void
    {
        if ($data && property_exists($data, "description")) {
            if (property_exists($data->description, "tags")) {
                $this->setTags($resource, $data->description->tags);
            }
        }
    }

    /**
     * @return Collection
     */
    public function getAll(): Collection
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
    public function exploreCourses(): Category
    {
        return Category::where('type', ResourceType::course)->get();
    }

    /**
     * @param DamResource $resource
     * @param $params
     * @return DamResource
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function update(DamResource $resource, $params): DamResource
    {
        if (array_key_exists("type", $params) && $params["type"]) {
            $updated = $resource->update(
                [
                    'type' => ResourceType::fromKey($params["type"])->value,
                ]
            );
        }

        if (array_key_exists("data", $params) && $params["data"]) {
            $updated = $resource->update(
                [
                    'data' => $params['data'],
                ]
            );
            $dataJson = json_decode($params["data"]);
            $this->linkCategoriesFromJson($resource, $dataJson);
            $this->linkTagsFromJson($resource, $dataJson);
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
        $newResource = DamResource::create(
            [
                'data' => $params['data'],
                'name' => $name,
                'type' => $type,
            ]
        );
        $jsonData = json_decode($params["data"]);
        $this->linkCategoriesFromJson($newResource, $jsonData);
        $this->saveAssociatedFiles($newResource, $params);
        $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($newResource));
        $newResource->refresh();
        $this->linkTagsFromJson($newResource, $jsonData);
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
    public function addPreview(DamResource $resource, $requestKey): DamResource
    {
        $this->mediaService->addFromRequest(
            $resource,
            $requestKey,
            MediaType::Preview()->key,
            ["parent_id" => $resource->id]
        );
        $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($resource));
        return $resource;
    }

    /**
     * @param DamResource $resource
     * @param $requestKey
     * @return DamResource
     */
    public function addFile(DamResource $resource, $requestKey): DamResource
    {
        $this->mediaService->addFromRequest(
            $resource,
            $requestKey,
            MediaType::File()->key,
            ["parent_id" => $resource->id]
        );
        return $resource;
    }

    /**
     * @param DamResource $resource
     * @param Category $category
     * @return DamResource
     * @throws Exception
     */
    public function addCategoryTo(DamResource $resource, Category $category): DamResource
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
     * @param array $tags
     * @return DamResource
     */
    public function setTags(DamResource $resource, array $tags = []): DamResource
    {
        $resource->setTags($tags);
        return $resource;
    }

    /**
     * Associated a category to a resource, always deletes the previous association
     * @param DamResource $resource
     * @param string $categoryName
     * @return DamResource
     * @throws Exception
     */
    public function setCategories(DamResource $resource, string $categoryName): DamResource
    {
        $category = Category::where("type", "=", $resource->type)->where("name", $categoryName)->first();
        if (null != $category) {
            $this->deleteCategoryFrom($resource, $category);
            $this->addCategoryTo($resource, $category);
        }
        return $resource;
    }

    /**
     * @param DamResource $resource
     * @param Category $category
     * @return DamResource
     * @throws Exception
     */
    public function deleteCategoryFrom(DamResource $resource, Category $category): DamResource
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
    public function addUse(DamResource $resource, $params): DamResource
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
    public function deleteUse(DamResource $resource, DamResourceUse $damResourceUse): DamResource
    {
        $resource->uses()->findOrFail($damResourceUse->id)->delete();
        return $resource;
    }
}
