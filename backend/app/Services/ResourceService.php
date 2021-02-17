<?php

namespace App\Services;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Models\Category;
use App\Models\DamResource;
use App\Models\DamResourceUse;
use App\Models\Media;
use App\Models\Organization;
use App\Services\Solr\SolrService;
use App\Utils\DamUrlUtil;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
     * @var CategoryService
     */
    private CategoryService $categoryService;

    /**
     * ResourceService constructor.
     * @param MediaService $mediaService
     * @param SolrService $solr
     * @param CategoryService $categoryService
     */
    public function __construct(MediaService $mediaService, SolrService $solr, CategoryService $categoryService)
    {
        $this->mediaService = $mediaService;
        $this->categoryService = $categoryService;
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
        $class->data = json_encode($resource->data);
        $class->name = $resource->name ?? '';
        $class->active = $resource->data->description->active;
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


    private function saveAssociateFile($type, $params, $model)
    {
        if (array_key_exists($type, $params) && $params[$type]) {
            if ($type === MediaType::Preview()->key) {
                // only one associated preview file is allowed
                $this->mediaService->deleteAllPreviews($model);
            }
            // If is a array of files, add a association from each item
            if (is_array($params[$type])) {
                foreach ($params[$type] as $file) {
                    $this->mediaService->addFromRequest(
                        $model,
                        null,
                        $type,
                        ["parent_id" => $model->id],
                        $file
                    );
                }
            } else {
                // If is not a array, associate file directly
                $this->mediaService->addFromRequest(
                    $model,
                    null,
                    $type,
                    ["parent_id" => $model->id],
                    $params[$type]
                );
            }
        }
    }

    /**
     * @param $model
     * @param $params
     */
    private function saveAssociatedFiles($model, $params): void
    {
        // Save Associated Files
        $this->saveAssociateFile(MediaType::File()->key, $params, $model);
        // Save Associated Previews
        $this->saveAssociateFile(MediaType::Preview()->key, $params, $model);
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
            $resource->update(
                [
                    'type' => ResourceType::fromKey($params["type"])->value,
                ]
            );
        }

        if (array_key_exists("data", $params) && !empty($params["data"])) {
            $resource->update(
                [
                    'data' => $params['data'],
                    'active' => $params['data']->description->active
                ]
            );
            $this->linkCategoriesFromJson($resource, $params['data']);
            $this->linkTagsFromJson($resource, $params['data']);
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

        $oid = Auth::user()->selected_organization;
        $wid = Auth::user()->selected_workspace;
        $org = Organization::find($oid);

        $name = array_key_exists('name', $params) ? $params["name"] : "";
        $type = ResourceType::fromKey($params["type"])->value;

        $newResource = DamResource::create(
            [
                'data' => $params['data'],
                'name' => $name,
                'type' => $type,
                'active' => $params['data']->description->active,
                'user_owner_id' => Auth::user()->id
            ]
        );

        $this->setResourceWorkspaceAndCollection($oid, $wid, $newResource, $org);
        //$newResource->save();
        $this->linkCategoriesFromJson($newResource, $params['data']);
        $this->saveAssociatedFiles($newResource, $params);
        $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($newResource));
        $newResource->refresh();
        $this->linkTagsFromJson($newResource, $params['data']);
        return $newResource;
    }

    public function setResourceWorkspaceAndCollection($oid, $wid, DamResource $newResource, $org): void
    {
        // if($params['collection_id']) {
        //     //Ahora este resource es accesible unicamente por la organización owner de la collection_id
        //     $newResource->collection_id = $params['collection_id'];
        // } else {
        //     //asignar a la colección pública o personal basado en user->selected_org/wsp
        // }

        if($oid && $wid) {
            $newResource->workspaces()->attach($wid);
        }
        //attach resource to public workspace or organization corporate workspace based on selected organization
        if($oid && $wid == null) {
            if($org->name == DefaultOrganizationWorkspace::public_organization) {
                $newResource->workspaces()->attach($org->publicWorkspace()->id);
            } else {
                $newResource->workspaces()->attach($org->corporateWorkspace()->id);
            }
        }
        //attach resource to personal workspace if
        if($oid == null && $wid || $oid == null && $wid == null) {
            if($wid && Auth::user()->workspaces()->where('id', $wid)) {
                $newResource->workspaces()->attach(Auth::user()->personalWorkspace()->id);
            } else {
                $newResource->workspaces()->attach(Auth::user()->personalWorkspace()->id);
            }
        }
    }

    /**
     * @param DamResource $resource
     * @throws Exception
     */
    public function delete(DamResource $resource)
    {
        $this->solr->deleteDocumentById($resource->id, $this->solr->getCollectionBySubType($resource->type));
        $resource->delete();
    }

    /**
     * @param DamResource $resource
     * @param array $params
     * @return DamResource
     */
    public function addPreview(DamResource $resource, array $params): DamResource
    {
        $this->saveAssociateFile(MediaType::Preview()->key, $params, $resource);
        $resource->refresh();
        $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($resource));
        return $resource;
    }

    /**
     * @param DamResource $resource
     * @param array $params
     * @return DamResource
     */
    public function addFile(DamResource $resource, array $params): DamResource
    {
        $this->saveAssociateFile(MediaType::File()->key, $params, $resource);
        $resource->refresh();
        $this->solr->saveOrUpdateDocument($this->prepareResourceToBeIndexed($resource));
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
        } else {
            // If category not exists, create it
            $category = $this->categoryService->store(["name" => $categoryName, "type" => $resource->type->key]);
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

    /**
     * @param DamResource $resource
     * @param array $ids
     * @return void
     * @throws Exception
     */
    public function deleteAssociatedFiles(DamResource $resource, array $ids): DamResource
    {
        foreach ($ids as $id) {
            $media = Media::findOrFail($id);
            $media->delete();
        }
        return $resource->refresh();
    }

    /**
     * @param DamResource $resource
     * @param Media $media
     * @return DamResource
     * @throws Exception
     */
    public function deleteAssociatedFile(DamResource $resource, Media $media): DamResource
    {
        $media->delete();
        return $resource->refresh();
    }
}
