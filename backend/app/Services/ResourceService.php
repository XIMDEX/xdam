<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Models\Category;
use App\Models\Collection as ModelsCollection;
use App\Models\DamResource;
use App\Models\DamResourceUse;
use App\Models\Media;
use App\Models\Workspace;
use App\Services\ExternalApis\Xowl\XowlQueue;
use App\Services\OrganizationWorkspace\WorkspaceService;
use App\Services\Solr\SolrService;
use App\Services\ExternalApis\KakumaService;
use App\Services\ExternalApis\XevalSyncActivityService;
use App\Services\ExternalApis\XevalSyncAssessmentService;
use App\Services\ExternalApis\Xowl\XtagsCleaner;
use App\Services\ExternalApis\XTagsService;
use App\Services\ExternalApis\Xowl\XowlImageService;
use App\Services\ExternalApis\XowlTextService;
use App\Utils\Texts;
use App\Utils\Utils;
use App\Utils\DamUrlUtil;
use DirectoryIterator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Services\Solr\SolrConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Stmt\Foreach_;
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
     * @var WorkspaceService
     */
    private WorkspaceService $workspaceService;

    /**
     * @var KakumaService
     */
    private KakumaService $kakumaService;

    /**
     * @var SolrConfig
     */
    private SolrConfig $solrConfig;

    /**
     * @var XTagService
     */
    private XTagsService $xtagService;

     /**

     * @var XowlImageService
     */
    private XowlImageService $xowlImageService;

    /** 
      * @var XevalSyncActivityService
      */
    private XevalSyncActivityService $xevalSyncActivityService;

    /** 
      * @var XevalSyncAssessmentService
      */
    private XevalSyncAssessmentService $xevalSyncAssessmentService;



    const PAGE_SIZE = 30;

    /**
     * ResourceService constructor.
     * @param MediaService $mediaService
     * @param SolrService $solr
     * @param CategoryService $categoryService
     */
    public function __construct(MediaService $mediaService, SolrService $solr, CategoryService $categoryService, WorkspaceService $workspaceService,
                                KakumaService $kakumaService, XTagsService $xtagService, XowlImageService $xowlImageService,SolrConfig $solrConfig,XevalSyncActivityService $xevalSyncActivityService, XevalSyncAssessmentService $xevalSyncAssessmentService)

    {
        $this->mediaService = $mediaService;
        $this->categoryService = $categoryService;
        $this->solr = $solr;
        $this->workspaceService = $workspaceService;
        $this->kakumaService = $kakumaService;
        $this->xtagService = $xtagService;
        $this->xowlImageService = $xowlImageService;
        $this->solrConfig = $solrConfig;
        $this->xevalSyncActivityService =  $xevalSyncActivityService;
        $this->xevalSyncAssessmentService = $xevalSyncAssessmentService;
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
                    if ($model->doesThisResourceSupportsAnAdditionalFile()) {
                        $this->mediaService->addFromRequest(
                            $model,
                            $type,
                            ["parent_id" => $model->id],
                            $file,
                            null
                        );
                    }
                }
            } else {
                if ($model->doesThisResourceSupportsAnAdditionalFile()) {
                    // If is not a array, associate file directly
                    $this->mediaService->addFromRequest(
                        $model,
                        $type,
                        ["parent_id" => $model->id],
                        $params[$type],
                        null
                    );
                }
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

    private function setDefaultLanguageIfNeeded(array $params): void
    {
        if( isset($params['type']) && $params["type"] === ResourceType::book && !property_exists($params["data"]->description, "lang")) {
            $params["data"]->description->lang = getenv('BOOK_DEFAULT_LANGUAGE');
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
    public function getAll($type = null, $ps = null, $withDeleted = false)
    {
        if ($withDeleted) {
            $query = DamResource::withTrashed();
        } else {
            $query = DamResource::query();
        }

        if (null == $ps && null == $type) {
            return $query->get();
        }

        if (null == $ps) {
            return $query->where('type', $type)->get();
        }

        if (null == $type) {
            return $query->paginate($ps);
        }

        return $query->where('type', $type)->paginate($ps);
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
     * @param String[] $query
     * return Collection
     */
    public function queryFilter($queryFilters)
    {

        return DamResource::whereRaw($queryFilters)->get();

    }

    /**
     * @return mixed
     */
    public function exploreCourses($user_id = null): Collection
    {
        $recommendedCategory = $this->getRecommendedCategory($user_id);
        $course = ResourceType::course;

        $categories = Category::where('type', $course)->orWhere('type', "=", strval($course))->get();

        $categories->prepend($recommendedCategory);

        return $categories;
    }

    public function getRecommendedCategory($user_id): array
    {
        if (!$user_id) {
            throw new Exception ("User id must be sent to get recommendations");
        }

        $courses = $this->kakumaService->getRecommendedCourses($user_id);

        return [
            'id' => "",
            'name' => 'Recommended for you',
            'resources' => $this->getRecommendedCourses($courses)
        ];
    }

    public function getRecommendedCourses($courses)
    {
        $resources = [];
        foreach ($courses as $course) {
            try {
                $damResource = DamResource::findOrFail($course);
                array_push($resources, $damResource);
            } catch (Exception $e) {
                continue;
            }
        }
        return $resources;
    }

    /**
     * @param DamResource $resource
     * @param $params
     * @return DamResource
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function update(DamResource $resource, $params): DamResource
    {
        DB::beginTransaction();
        try {
            $params['data'] = json_decode($params['data']);
            if (array_key_exists("FilesToRemove", $params)) {
                foreach ($params["FilesToRemove"] as $mediaID) {
                    $mediaResult = Media::where('id', $mediaID)->first();
                    if ($mediaResult !== null) {
                        $this->deleteAssociatedFile($resource, $mediaResult);
                    }
                }
            }
            if (array_key_exists("type", $params) && $params["type"]) {
                $resource->update(
                    [
                        'type' => ResourceType::fromKey($params["type"])->value,
                    ]
                );
            }
            if (array_key_exists("data", $params) && !empty($params["data"])) {
    
                $this->setDefaultLanguageIfNeeded($params);
                if (isset($params['data']->description->entities_linked) || isset($params['data']->description->entities_non_linked)) {
                    if (isset($params['data']->description->entities_linked)) {
                        unset($params['data']->description->entities_linked);
                    }
                    if (isset($params['data']->description->entities_non_linked)) {
                        unset($params['data']->description->entities_non_linked);
                    }
                }
    
                $resource->update(
                    [
                        'data' => $params['data'],
                        'active' => $params['data']->description->active,
                        'name' => $params['data']->description->name ?? 'name not found'
                    ]
                );
    
                $this->linkCategoriesFromJson($resource, $params['data']);
                $this->linkTagsFromJson($resource, $params['data']);
            }
    
            // TODO save all languages label on taxon path
            if (isset($params['data']->description->semantic_tags)) {
                $semantic_tags = $params['data']->description->semantic_tags;
                $lom_params = [
                    'Taxon Path'=> [],
                    '_tab_key' => "9"
                ];
                /*foreach ($semantic_tags as $semantic_tag) {
                    $lom_params['Taxon Path'][] = [
                        'Id' => $semantic_tag->id,
                        'Entry' => $semantic_tag->name
                    ];
                }
    
                // TODO save on xTags
                $lang = App::getLocale();
                if (isset($resource->data->description->semantic_tags)) {
                    $this->xtagService->saveXTagsResource(
                        $resource->id,
                        'semantic_tags',
                        $lang,
                        $resource->data->description->semantic_tags
                    );
                }*/
                $this->setLomData($resource, $lom_params);
            }
    
            $this->saveAssociatedFiles($resource, $params);
          
            $resource = $resource->fresh();
            $this->solr->saveOrUpdateDocument($resource);
            if (isset($params['File'][0]) || isset($params['Preview'])) {
                $XowlQueue = new XowlQueue($this->mediaService, $this->xowlImageService);
            }
            if (isset($params['File'][0])) {
                $mediaFiles = $resource->getMedia('File');
                $XowlQueue->addDocumentToQueue($mediaFiles);
                $XowlQueue->addImageToQueue($mediaFiles);
            }
            if (isset($params['Preview'])) {
                $mediaFiles = $resource->getMedia('Preview');
                $XowlQueue->addImageToQueue($mediaFiles);
            }
            $mediaFiles = $resource->getMedia('Preview');
            if( $params['type'] === "activity"){
                $parseActivity = $this->xevalSyncActivityService->parseActivityData($resource->id,$params['data'],$params['collection_id']);
                $res = $this->xevalSyncActivityService->syncActivityOnXeval($parseActivity);
            }
            if ($params['type'] === "assessment") {
                $parseAssessment =  $this->xevalSyncAssessmentService->parseAssessmentData($resource->id,$params['data'],$params['collection_id']);
                $res = $this->xevalSyncAssessmentService->syncAssessmentOnXeval( $parseAssessment);
            }
            DB::commit();
            return $resource;
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
       
    }

    public function updateFromXeval(DamResource $resource,$params){
        DB::beginTransaction();
        try {
            $params['data'] = json_decode($params['data']);
            if (array_key_exists("type", $params) && $params["type"]) {
                $resource->update(
                    [
                        'type' => ResourceType::fromKey($params["type"])->value,
                    ]
                );
            }
            if (array_key_exists("data", $params) && !empty($params["data"])) {
    
                $this->setDefaultLanguageIfNeeded($params);
    
             
                $json_data_xdam = $resource->data->description;
                $json_data_xeval = $params['data']->description;
                $merged_json = (object) array_merge((array) $json_data_xdam, (array) $json_data_xeval);
                $params['data']->description = $merged_json;
                $resource->update(
                    [
                        'data' => $params['data'],
                        'active' => $params['data']->description->active,
                        'name' => $params['data']->description->name ?? 'name not found'
                    ]
                );
                $this->linkCategoriesFromJson($resource, $params['data']);
                $this->linkTagsFromJson($resource, $params['data']);
            }
            if (isset($params['data']->description->semantic_tags)) {
                $lom_params = [
                    'Taxon Path'=> [],
                    '_tab_key' => "9"
                ];
                $this->setLomData($resource, $lom_params);
            }
            $this->saveAssociatedFiles($resource, $params);
            $resource = $resource->fresh();
            $this->solr->saveOrUpdateDocument($resource);
           
            DB::commit();
            return $resource;
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }

      /**
     * @param DamResource $resource
     * @param $params
     * @return DamResource
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function patch(DamResource $resource, $params): DamResource
    {
        if (array_key_exists("type", $params) && $params["type"]) {
            $resource->update(
                [
                    'type' => ResourceType::fromKey($params["type"])->value,
                ]
            );
            unset($params["type"]);
        }

        if (array_key_exists("data", $params) && gettype($params["data"]) == "string") {
            $params["data"] = json_decode($params["data"]);
        }

        $resource->update($params);


        $this->saveAssociatedFiles($resource, $params);
        $resource = $resource->fresh();
        $this->solr->saveOrUpdateDocument($resource);
        return $resource;
    }

    /**
     * @param $params
     * @return DamResource
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function store(
        $params,
        $toWorkspaceId = null,
        $fromBatchType = null,
        $launchThrow = true
    ): DamResource

    {
        $params['data'] = json_decode($params['data']);
        /*
            $wid cannot be null
        */
        $wsp = null;
        $paramsData =  is_array($params['data']) ?  Utils::arrayToObject($params['data']) : $params['data'];
        $exceptionStrings = ['notWorkspace' => 'Undefined workspace','notOrganization' => 'The workspace doesn\'t belong to an organization'];
        $wsp  = $toWorkspaceId ? Workspace::find($toWorkspaceId) : Workspace::find(Auth::user()->selected_workspace);
        $name = array_key_exists('name', $params) ? $params["name"] : "";
        $type = $fromBatchType ?? ResourceType::fromKey($params["type"])->value;
        $idResourceData = ($type == ResourceType::course) ? $params['kakuma_id'] : Str::orderedUuid() ;
        $nameResource   = $paramsData->description->name ?? $name;

        if($wsp === null) throw new Exception($exceptionStrings['notWorkspace']);
        if(!$wsp->organization()->first()) throw new Exception($exceptionStrings['notOrganization']);

        $this->setDefaultLanguageIfNeeded($params);

        $resource_data = [
            'id'   => $idResourceData,
            'data' => $paramsData,
            'name' => $nameResource,
            'type' => $type,
            'active' =>  $paramsData->description->active,
            'user_owner_id' => Auth::user()->id,
            'collection_id' => $params['collection_id'] ?? null
        ];
        //TODO: Improve this part

        // ($type == ResourceType::document && isset($params->description->uuid) && null != $params->description->uuid) $resource_data['id'] = $params['data']['description']->uuid;

        $newResource = false;
        try {

            $newResource = DamResource::create($resource_data);
            // $_newResource = $newResource;

            $this->setResourceWorkspace($newResource, $wsp);
            $this->linkCategoriesFromJson($newResource,$paramsData );
            $this->linkTagsFromJson($newResource,$paramsData);
            $this->saveAssociatedFiles($newResource, $params);
            $newResource = $newResource->fresh();
            $this->solr->saveOrUpdateDocument($newResource);


            if (isset($params['File'][0]) || isset($params['Preview'])) {
                $mediaService = new MediaService();
                $xowlImageService = new XowlImageService('ES');
                $XowlQueue = new XowlQueue($mediaService, $xowlImageService);
            }

            if (isset($params['File'][0])) {
                $mediaFiles = $newResource->getMedia('File');
                $XowlQueue->addDocumentToQueue($mediaFiles);
                $XowlQueue->addImageToQueue($mediaFiles);
            }

            if (isset($params['Preview'])) {
                $mediaFiles = $newResource->getMedia('Preview');
                $XowlQueue->addImageToQueue($mediaFiles);
            }
            $_newResource = false;
            return $newResource;
        } catch (\Exception $th) {
            $this->solr->deleteDocument($newResource);
            $newResource->forceDelete();
            throw $th;
        }
    }


    public function resourcesSchema ()
    {
        $path = storage_path('solr_validators');
        $dir = new DirectoryIterator($path);
        $schemas = [];
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $fileName = $fileinfo->getFilename();
                $json_file = file_get_contents($path .'/'. $fileName);
                $key = str_replace('.json', '', $fileName);
                $resourceName = ucfirst($this->solrConfig->getNameCoreConfig(str_replace('_validator', '', $key)));
                $json = json_decode($json_file);

                if (class_exists("App\\Services\\{$resourceName}Service")) {
                    $resource_service = app("App\\Services\\{$resourceName}Service");
                    $json = $resource_service::handleSchema($json);
                }
                $schemas[$key] = $json;
                if (isset($schemas[$key]->properties->description->properties)) {
                    foreach ($schemas[$key]->properties->description->properties as $key_prop => $prop) {
                        $schemas[$key]->properties->description->properties->$key_prop->title = Texts::web($key_prop, null, [], $prop->title ?? $key_prop);
                    }
                }
            }
        }


        $path = storage_path('collection_config');
        $dir = new DirectoryIterator($path);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $fileName = $fileinfo->getFilename();
                $json_file = file_get_contents($path .'/'. $fileName);
                $key = str_replace('.json', '', $fileName);
                $schemas['collection_config'][$key] = json_decode($json_file);
            }
        }

        return $schemas;
    }

    private function searchPreviewImage($data, $name): ?UploadedFile
    {
        $fileName = str_replace('.', '_', $name).'_preview';

        return array_key_exists($fileName, $data) ? $data[$fileName] : null;
    }

    public function storeBatch ($data)
    {
        $collection = ModelsCollection::find($data['collection']);
        $organization = $collection->organization()->first();

        if($data['create_wsp'] === '1') {
            $wsp = $this->workspaceService->create($organization->id, $data['workspace'])->id;
        } else {
            $wsp = $data['workspace'];
        }

        $genericResourceDescription = array_key_exists('generic', $data) ? json_decode($data['generic'], true) : [];

        $especificFilesInfoMap = array_key_exists('filesInfo', $data) ? json_decode($data['filesInfo'], true) : [];

        $createdResources = [];

        //$supported_mime_types = $this->resourcesSchema();

        //$supported_mime_types[$collection->accept]

        foreach ($data['files'] as $file) {
            $name = $file->getClientOriginalName();
            $type = explode('/', $file->getMimeType())[0];

            $specificInfo = array_key_exists($name, $especificFilesInfoMap) ? $especificFilesInfoMap[$name] : [];

            $description = array_merge(
                [
                    'name' => $name,
                    'active' => false,
                ],
                $genericResourceDescription,
                $specificInfo,
            );

            $params = [
                'data' => [
                    'description' => $description
                ],
                'collection_id' => $collection->id,
                'File' => [$file],
                'Preview' => $this->searchPreviewImage($data, $name),
            ];
            $resource = $this->store($params, $wsp, $collection->accept === ResourceType::multimedia ? $type : $collection->accept);
            $createdResources[] = $resource;
        }

        return $createdResources;
    }

    public function lomesSchema($asArray = false)
    {
        $lomesSchema =  Utils::getLomesSchema($asArray);

        $lomesSchemaClient = array_keys(config('solr_facets.client.'.env('APP_CLIENT', 'DEFAULT')));

        if (count($lomesSchemaClient) > 0) {
            $tabsToDel = [];
            if (!$asArray) {
                foreach ($lomesSchema->tabs as $index => $keys) {
                    foreach ($keys->properties as $name => $property) {
                        if (!in_array($property->data_field, $lomesSchemaClient)) {
                            unset($lomesSchema->tabs[$index]->properties->$name);
                        }
                    }
                    if (count((array) $keys->properties) === 0) {
                        $tabsToDel[] = $index;
                    }
                }
                foreach ($tabsToDel as $tabToDel) {
                    $lomesSchema->tabs[$tabToDel]->hide = true;
                }
            } else {
                foreach ($lomesSchema['tabs'] as $index => $keys) {
                    foreach ($keys['properties'] as $name => $property) {
                        if (!in_array($property['data_field'], $lomesSchemaClient)) {
                            unset($lomesSchema['tabs'][$index]['properties'][$name]);
                        }
                    }
                    if (count($keys['properties']) === 0) {
                        $tabsToDel[] = $index;
                    }
                }
                foreach ($tabsToDel as $tabToDel) {
                    $lomesSchema['tabs'][$tabToDel]['hide'] = true;
                }
            }
        }
        if (count($tabsToDel) > 0) $lomesSchema->tabs = array_values($lomesSchema->tabs);

        return $lomesSchema;
    }

    public function lomSchema($asArray = false)
    {
        /*$json_file = file_get_contents(storage_path('/lom') . '/lomSchema.json');
        $schema = json_decode($json_file, $asArray);
        return $schema;*/
        return Utils::getLomSchema($asArray);
    }

    public function searchForAssociativeKey($key, $tabKey, $array )
    {
        //move to Utils
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
        $tabSchema = $this->searchForAssociativeKey('key', $tabKey, $lomesSchema['tabs']);

        foreach ($tabSchema['properties'] as $label => $props) {
            foreach ($formData as $f_key => $f_value) {
                if($f_key === $label && $f_value !== null) {
                    $updateArray[$props['data_field']] = $f_value;
                }
            }
        }

        $dam_lomes->update($updateArray);
        $dam_lomes->save();
        $this->solr->saveOrUpdateDocument($damResource, null, true);
    }

    public function setLomData($damResource, $params)
    {
        $dam_lom = $damResource->lom()->firstOrCreate();
        $updateArray = [];
        if ($params instanceof Request) {
            $formData = $params->all();
        } else {
            $formData = $params;
        }
        $tabKey = $formData['_tab_key'];
        $lomSchema = $this->lomSchema(true);
        $tabSchema = $this->searchForAssociativeKey('key', $tabKey, $lomSchema['tabs']);

        foreach ($tabSchema['properties'] as $label => $props) {
            foreach ($formData as $f_key => $f_value) {
                if($f_key === $label && $f_value !== null) {
                    $updateArray[$props['data_field']] = $f_value;
                }
            }
        }

        $dam_lom->update($updateArray);
        $dam_lom->save();
        $this->solr->saveOrUpdateDocument($damResource, null, true);
    }

    public function getLomesData($damResource)
    {
        $schema = $this->lomesSchema(true);
        $lomes = $damResource->lomes()->first();
        if(!$lomes) {
            return [];
        }
        $lomes = $lomes->toArray();
        $response = $this->getDataFromSchema($lomes, $schema);
        return $response;
    }

    public function getLomData($damResource)
    {
        $schema = $this->lomSchema(true);
        $lom = $damResource->lom()->first();
        if(!$lom) {
            return [];
        }
        $lom = $lom->toArray();
        $response = $this->getDataFromSchema($lom, $schema);
        return $response;
    }

    private function getDataFromSchema($data, $schema)
    {
        $response = [];

        foreach ($schema['tabs'] as $tab_key => $tab_values) {
            $response[$tab_values['key']] = [
                'title' => $tab_values['title'],
                'key' => $tab_values['key'],
                'formData' => []
            ];
            foreach ($tab_values['properties'] as $prop_label => $prop_values) {
                $response[$tab_values['key']]['formData'][$prop_label] = [
                    'data_field' => $prop_values['data_field'],
                    'type' => $prop_values['type']
                ];
            }

        }

        foreach ($data as $db_field => $value) {
            foreach ($response as $key => $arr_v) {
                foreach ($arr_v['formData'] as $label => $res_db_field) {
                    $data_field = isset($res_db_field["data_field"]) ? $res_db_field["data_field"] : $res_db_field;
                    if ($db_field == $data_field) {
                        if (null === $value) {
                            unset($response[$key]['formData'][$label]);
                        } else {
                            if (isset($res_db_field['type']) && strpos('json', $res_db_field['type']) || $res_db_field['type'] == 'array') $value = json_decode($value);
                            $response[$key]['formData'][$label] = $value;
                        }
                    }
                    }
                if (count($response[$key]['formData']) === 0) unset($response[$key]);
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
    public function delete($resourceId)
    {
        $resource = DamResource::withTrashed()->findOrFail($resourceId);
        if($resource->type === "activity"){
            $data = $resource->data;
            $data->description->status_id = 3;
            $parseActivity = $this->xevalSyncActivityService->parseActivityData($resource->id,$data,$resource->collection_id);
            $res = $this->xevalSyncActivityService->syncActivityOnXeval($parseActivity);
        }
        try {
            $this->solr->deleteDocument($resource);
            $resource->forceDelete();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }

    }

    /**
     * @param DamResource $resource
     * @param bool $force
     * @param bool $onlyLocal -> if true, only deletes in dam and not in external services
     * @throws Exception
     */
    public function softDelete(DamResource $resource, bool $force, bool $onlyLocal)
    {
        if ($resource->type == ResourceType::course && !$onlyLocal) {
            $this->kakumaService->softDeleteCourse($resource->id, $force);
        }

        $resource->delete();
        $this->solr->saveOrUpdateDocument($resource);

        return true;

    }

    /**
     * @param $resourceId
     * @param bool $onlyLocal -> if true, only deletes in dam and not in external services
     * @throws Exception
     */
    public function restore($resourceId, bool $onlyLocal)
    {
        $resource = DamResource::withTrashed()->findOrFail($resourceId);
        if ($resource->type == ResourceType::course && !$onlyLocal) {
            $this->kakumaService->restoreCourse($resource->id);
        }

        $resource->restore();
        $this->solr->saveOrUpdateDocument($resource);

        return true;

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
        $this->solr->saveOrUpdateDocument($resource);
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
        $this->solr->saveOrUpdateDocument($resource);
        return $resource->refresh();
    }

    public function updateAsLast($toBeCloned, $created) {
        $collection = $toBeCloned->collection()->first();
        $theClon = $collection->resources()->orderBy($created ? 'created_at' : 'updated_at', 'desc')->first();
        $toBeCloned->data = $theClon->data;
        $toBeCloned->save();
        $this->solr->saveOrUpdateDocument($toBeCloned);
        return $toBeCloned;
    }

    public function updateAsOther($toBeCloned, $theClon) {

    }

    public function getResource($resourceID, $collectionID)
    {
        $resource = DamResource::where('id', $resourceID)
                        ->where('collection_id', $collectionID)
                        ->first();

        return $resource;
    }

    public function getCollection($collectionID)
    {
        $collection = ModelsCollection::where('id', $collectionID)
                        ->first();

        return $collection;
    }

    public function countResources($type = null)
    {
        $query = (null !== $type) ? DamResource::where('type', $type) : DamResource::all();
        return $query->count();
    }
}
