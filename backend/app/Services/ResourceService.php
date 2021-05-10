<?php

namespace App\Services;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Models\Category;
use App\Models\DamResource;
use App\Models\DamResourceUse;
use App\Models\Lomes;
use App\Models\Media;
use App\Models\Workspace;
use App\Services\Solr\SolrService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
    private function saveAssociatedFiles(DamResource $model, $params): void
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
                        if(count($property) > 0) {
                            foreach ($property as $child) {
                                $this->setCategories($resource, $child, $data);
                            }
                        } else {
                            foreach ($resource->categories()->get() as $k => $cat) {
                                $resource->categories()->detach($cat);
                                $resource->save();
                            }
                        }
                    } else {
                        $this->setCategories($resource, $property, $data);
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
    public function linkTagsFromJson($resource, $data): void
    {
        if ($data && property_exists($data, "description")) {
            if (property_exists($data->description, "skills")) {
                $this->setTags($resource, $data->description->skills);
            }
            if (property_exists($data->description, "tags")) {
                $this->setTags($resource, $data->description->tags);
            }
        }
    }

    /**
     * @param null $type
     * @return Collection
     */
    public function getAll($type = null)
    {
        return $type ? DamResource::where('type', $type)->get() : DamResource::all();
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
    public function exploreCourses(): Collection
    {
        $course = ResourceType::course;
        return Category::where('type', $course)->orWhere('type', "=", strval($course))->get();
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
        $this->solr->saveOrUpdateDocument($resource);
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
        /*
            $wid cannot be null
        */
        if($wid = Auth::user()->selected_workspace) {
            $wsp = Workspace::find($wid);
            $org = $wsp->organization()->first();
        } else {
            throw new Exception('No workspace selected.');
        }

        $name = array_key_exists('name', $params) ? $params["name"] : "";
        $type = ResourceType::fromKey($params["type"])->value;

        $resource_data = [
            'data' => $params['data'],
            'name' => $params['data']->description->name ?? $name,
            'type' => $type,
            'active' => $params['data']->description->active,
            'user_owner_id' => Auth::user()->id,
            'collection_id' => $params['collection_id'] ?? null
        ];

        if ($type == ResourceType::course) {
            $resource_data['id'] = $params['kakuma_id'];
        } else {
            $resource_data['id'] = Str::orderedUuid();
        }

        $newResource = DamResource::create($resource_data);

        isset($org) && $wid ? $this->setResourceWorkspace($newResource, $wsp) : null;
        $this->linkCategoriesFromJson($newResource, $params['data']);
        $this->linkTagsFromJson($newResource, $params['data']);
        $this->saveAssociatedFiles($newResource, $params);
        $this->solr->saveOrUpdateDocument($newResource);
        $newResource->refresh();
        $this->linkTagsFromJson($newResource, $params['data']);
        return $newResource;
    }

    public function lomesSchema ($asArray = false)
    {
        $json_file = file_get_contents(storage_path('/lomes') .'/lomesSchema.json');
        $schema = json_decode($json_file, $asArray);
        return $schema;
    }

    public function searchForAssociativeKey($key, $tabKey, $array ) {
        foreach ($array as $k => $val) {
            if ($val[$key] === $tabKey) {
                return $array[$k];
            }
        }
        return null;
     }

    public function setLomesData($damResource, $params)
    {
        $dam_lomes = $damResource->lomes()->firstOrCreate();
        $updateArray = [];
        $formData = $params->all();
        $tabKey = $formData['_tab_key'];
        $lomesSchema = $this->lomesSchema(true);
        //$tabSchema = array_search($tabKey, $lomesSchema['tabs']);
        $tabSchema = $this->searchForAssociativeKey('key', $tabKey, $lomesSchema['tabs']);
        foreach ($tabSchema['properties'] as $label => $props) {
            foreach ($formData as $f_key => $f_value) {
                if($f_key === $label && $f_value !== null) {
                    $updateArray[$props['data_field']] = $f_value;
                }
            }
        }
        echo '';
        $dam_lomes->update($updateArray);
        $dam_lomes->save();
    }

    public function getLomesData($damResource)
    {
        $schema = $this->lomesSchema(true);
        $lomes = $damResource->lomes()->first();
        if(!$lomes) {
            return [];
        }
        $lomes = $lomes->toArray();
        $response = [];

        foreach ($schema['tabs'] as $tab_key => $tab_values) {
            $response[$tab_values['key']] = [
                'title' => $tab_values['title'],
                'key' => $tab_values['key'],
                // 'db_field' => $tab_values['data_field'],
                'formData' => []
            ];
            foreach ($tab_values['properties'] as $prop_label => $prop_values) {

                $response[$tab_values['key']]['formData'][$prop_label] = $prop_values['data_field'];
            }

        }
        foreach ($lomes as $db_field => $value) {
            foreach ($response as $key => $arr_v) {
                foreach ($arr_v['formData'] as $label => $res_db_field) {
                    if ($db_field == $res_db_field) {
                        $response[$key]['formData'][$label] = $value;
                    }
                }
            }
        }

        return $response;






    }

    public function setResourceWorkspace(DamResource $newResource, Workspace $wsp): void
    {
        $newResource->workspaces()->attach($wsp);
    }

    /**
     * @param DamResource $resource
     * @throws Exception
     */
    public function delete(DamResource $resource)
    {
        try {
            $this->solr->deleteDocument($resource);
            $resource->delete();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }

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
        $this->solr->saveOrUpdateDocument($resource);
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
        $this->solr->saveOrUpdateDocument($resource);
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
        $this->solr->saveOrUpdateDocument($resource);
        return $resource;
    }

    public function setOnlyOneCategoryTo(DamResource $resource, Category $category): DamResource
    {
        if ($category->type == $resource->type) {
            foreach($resource->categories()->get() as $cat) {
                $resource->categories()->detach($cat);
            }
            $resource->categories()->attach($category);
        } else {
            throw new Exception ("category type and resource type are not equals");
        }
        $this->solr->saveOrUpdateDocument($resource);
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
    public function setCategories(DamResource $resource, string $categoryName, $data = null): DamResource
    {
        $category = Category::where("type", "=", $resource->type)->where("name", $categoryName)->first();

        $is_course = ($resource->type == 'course');
        if (null != $category) {
            $this->deleteCategoryFrom($resource, $category);
            $is_course ? $this->setOnlyOneCategoryTo($resource, $category) : $this->addCategoryTo($resource, $category);
        } else {
            // If category not exists, create it
            $category = $this->categoryService->store(["name" => $categoryName, "type" => $resource->type]);
            $is_course ? $this->setOnlyOneCategoryTo($resource, $category) : $this->addCategoryTo($resource, $category);
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
                $this->solr->saveOrUpdateDocument($resource);
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
