<?php


namespace App\Services\Solr;

use App\Models\Collection;
use App\Models\DamResource;
use App\Models\Lom;
use App\Models\Lomes;
use App\Models\Workspace;
use App\Http\Resources\Solr\LOMSolrResource;
use App\Services\Catalogue\FacetManager;
use App\Utils\Utils;
use Exception;
use Illuminate\Support\Facades\Storage;
use Solarium\Client;
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Solarium\Core\Query\Result\ResultInterface;
use Illuminate\Database\Eloquent\Model;
use stdClass;
use App\Http\Resources\Solr\{ActivitySolrResource, AssessmentSolrResource, BookSolrResource, CourseSolrResource, DocumentSolrResource, MultimediaSolrResource};
use App\Models\CDN;
use DOMDocument;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Response;
use Solarium\Core\Client\Adapter\AdapterHelper;

/**
 * Class that is responsible for making crud with Apache Solr and each of its instances
 * Class SolrService
 * @package App\Services\Solr
 */
class SolrService
{

    const LOM = 'lomes';

    private FacetManager $facetManager;
    private SolrConfig $solrConfig;
    /** @var Client[] $clients  */
    private array $clients;

    /**
     * SolrService constructor.
     * @param FacetManager $facetManager
     * @param SolrConfig $solrConfig
     */
    public function __construct(FacetManager $facetManager, SolrConfig $solrConfig)
    {
        $this->facetManager = $facetManager;
        $this->solrConfig = $solrConfig;
        $this->clients = $solrConfig->getClients();
        $this->solrConfigReque = $solrConfig;
    }

    /**
     * returns the document that will be finally indexed in solr
     * @param DamResource $resource
     * @param $resourceClass
     * @return array
     */
    private function getDocumentFromResource(
        DamResource $resource,
        $resourceClass): array
    {

        return json_decode((new $resourceClass($resource,true))->toJson(), true);
    }

    /**
     * given a collection returns the required solr client instance
     * @param Collection $collection
     * @return Client
     * @throws Exception
     */
    private function getClientFromCollection(Collection $collection)
    {
        $connection = $collection->solr_connection;

        if ($connection) {
            if (!array_key_exists($connection, $this->clients)) {
                $connection = $this->getCoreNameVersioned($connection);
            }
            if (array_key_exists($connection, $this->clients)) {
                return $this->clients[$connection];
            } else {
                throw new Exception("there is no client for the collection $collection->id");
            }
        } else {
            throw new Exception("The collection $collection->id does not have a connection_name configured");
        }
    }

    /**
     * given a resource, returns the required solr client instance
     * @param DamResource $damResource
     * @param integer $attempt
     * @return mixed
     * @throws Exception
     */
    public function getClientFromResource(DamResource $damResource, $attempt = 0)
    {
        $client = null;

        try {
            $client = $this->getClientFromCollection($damResource->collection);
        } catch (\Exception $ex) {
            // echo $ex->getMessage();

            if ($attempt < 30) {
                sleep(10);
                $client = $this->getClientFromResource($damResource, $attempt + 1);
            }
        }

        return $client;
    }

    /**
     * Gets a Solr client
     * @param string $client
     * @return Client
     * @throws Exception
     */
    public function getClient(string $client)
    {
        if (!array_key_exists($client, $this->clients)) {
            $client = $this->getCoreNameVersioned($client);
        }
        if (!array_key_exists($client, $this->clients)) {
            throw new Exception("There is no client $client " . json_encode($this->clients));
        }

        return $this->clients[$client];
    }

    /**
     * Updates or saves a document in Solr
     * @param Client $client
     * @param array $documentFound
     * @return ResultInterface
     * @throws Exception
     */
    private function saverOrUpdateSolrDocument(
        Client $client,
        array $documentFound
    ): ResultInterface {
        // Retrieve existing document from Solr by ID
        $query = $client->createSelect();
        $query->setQuery('id:' . $documentFound['id']);
        $resultSet = $client->select($query);

        // Prepare the update command
        $createCommand = $client->createUpdate();
        $newDocument = $createCommand->createDocument();

        if ($resultSet->getNumFound() > 0) {
            $existingDocument = $resultSet->getDocuments()[0];
            // Copy fields from the existing document
            foreach ($existingDocument as $key => $value) {
                $newDocument->$key = $value;
            }
        }

        // Merge new fields
        foreach ($documentFound as $key => $value) {
            $newDocument->$key = $value;
        }

        $createCommand->addDocument($newDocument);
        $createCommand->addCommit();

        // Execute the update
        return $client->update($createCommand);
    }

    /**
     * Saves LOM documents
     * @param Client $client
     * @param $element
     * @param array $schema
     * @param DamResource $damResource
     * @throws Exception
     */
    private function saveLOMDocuments(
        Client $client,
        $element,
        array $schema,
        DamResource $damResource
    ) {
        // Checks if the element is null
        if ($element !== null) {
            // Deletes the current Solr documents
            $this->deleteSolrDocument($client, 'dam_resource_id:' . $damResource->id);

            // Gets the LOM attributes
            $lomValues = $element->getResourceLOMValues();

            // Iterates through the attributes
            foreach ($lomValues as $lomItem) {
                $resource = new LOMSolrResource(
                    $element,
                    $damResource,
                    $lomItem['key'],
                    $lomItem['value'],
                    $lomItem['subkey']
                );
                $documentFound = json_decode($resource->toJson(), true);
                $this->saverOrUpdateSolrDocument($client, $documentFound);
            }
        }
    }

    /**
     * update or save a document in solr
     * @param DamResource $damResource
     * @param string $solrVersion
     * @param bool $reindexLOM
     * @return ResultInterface
     * @throws Exception
     */
    public function saveOrUpdateDocument(
        DamResource $damResource,
        $solrVersion = null,
        $reindexLOM = false
    ): ResultInterface {
        // Gets the current core version used, and updates the clients
        $solrVersion = $this->getCoreVersion($solrVersion);
        $this->clients = $this->solrConfig->updateSolariumClients($solrVersion);


        // Gets the client attached to the current resource
        $client = $this->getClientFromResource($damResource);

        // Gets the current resource document, and updates it
        $documentResource = $this->getDocumentFromResource($damResource, $client->getOption('resource'));
        return $this->saverOrUpdateSolrDocument($client, $documentResource);
    }

    /**
     * Deletes a document in Solr
     * @param Client $client
     * @param string $query
     * @return ResultInterface
     * @throws Exception
     */
    private function deleteSolrDocument(Client $client, string $query): ResultInterface
    {
        $deleteQuery = $client->createUpdate();
        $deleteQuery->addDeleteQuery($query);
        $deleteQuery->addCommit();
        return $client->update($deleteQuery);
    }

    /**
     * Delete a document in Solr
     * @param DamResource $damResource
     * @return ResultInterface
     * @throws Exception
     */
    public function deleteDocument(DamResource $damResource): ResultInterface
    {
        $this->clients = $this->solrConfig->updateSolariumClients($this->getCoreVersion(null));

        try {
            $lomClient = $this->getClient('lom');
            $lomesClient = $this->getClient('lomes');
        } catch (\Exception $ex) {
            // echo $ex->getMessage();
            $lomClient = null;
            $lomesClient = null;
        }

        if ($lomClient !== null && $lomesClient !== null) {
            $this->deleteSolrDocument($lomClient, 'dam_resource_id:' . $damResource->id);
            $this->deleteSolrDocument($lomesClient, 'dam_resource_id:' . $damResource->id);
        }

        $client = $this->getClientFromResource($damResource);
        return $this->deleteSolrDocument($client, 'id:' . $damResource->id);
    }

    private static function paginateResults($results)
    {
        /* Response with pagination data */
        $response = new \stdClass();
        $response->facets = $results['facets'];
        $response->current_page = $results['currentPage'];
        $response->data = $results['documentsResponse'];
        $response->per_page = $results['limit'];
        $response->last_page = $results['totalPages'];
        $response->next_page = $results['nextPage'];
        $response->prev_page = $results['prevPage'];
        $response->total = $results['documentsFound'];
        return $response;
    }

    /**
     * Make a faceted query with parameters to Apache Solr
     * @param array $pageParams
     * @param array $sortParams
     * @param $facetsFilter
     * @param $collection
     * @return stdClass
     * @throws Exception
     */
    public function paginatedQueryByFacet(
        $pageParams = [],
        $sortParams = [],
        $facetsFilter,
        $collection
    ): stdClass {
        // Gets the results
        $results = $this->executeSearchQuery($pageParams, $sortParams, $facetsFilter, $collection);
        return $this->paginateResults($results);
    }

    public function distributedPaginatedQueryByFacet(
        $pageParams = [],
        $sortParams = [],
        $facetsFilter,
        $workspace
    ): stdClass {
        $results = $this->executeDistributedSearchQuery($pageParams, $sortParams, $facetsFilter, $workspace);
        return $this->paginateResults($results);
    }

    private static function updateFacetsFilter(&$facetsFilter)
    {
        //we need to replace all strings with spaces to  "\ " otherwise, Solr don't recognize it.
        foreach ($facetsFilter as $key => $value) {
            if (is_string($value)) {
                $facetsFilter[$key] = str_replace(" ", "\ ", $value);
            }

            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (is_string($v)) {
                        $facetsFilter[$key][$k] = str_replace(" ", "\ ", $v);
                    }
                }
            }
        }
    }

    private function executeSearchQuery($pageParams = [], $sortParams = [], $facetsFilter, $collection)
    {
        // Updates the facets filter
        $this->updateFacetsFilter($facetsFilter);
        $this->clients = $this->solrConfig->updateSolariumClients($this->getCoreVersion(null));
        $client = $this->getClientFromCollection($collection);
        $core = $collection->accept;
        $classCore = $client->getOptions()['classHandler'];
        $search = $pageParams['search'];
        $currentPage = $pageParams['currentPage'];
        $limit = $pageParams['limit'];

        $query = $client->createSelect();
        $facetSet = $query->getFacetSet();

        $list3 = $this->getAllFieldsFromCore($core);
        // The facets to be applied to the query
        $this->facetManager->setFacets($facetSet, [], $core, $list3);
        // Limit the query to facets that the user has marked us
        $this->facetManager->setQueryByFacets($query, [], $core, $list3);

        /* if we have a search param, restrict the query */
        if (!empty($search)) {
            $search = urldecode($search);
            $term = '';
            $phrase = '';
            $terms = explode('"', $search);

            if (count($terms) < 2) {
                $term = $search;
            } else {
                $startWithPhrase = $terms[0] === '';
                foreach ($terms as $idx => $element) {
                    if (!$startWithPhrase && $idx == 0) {
                        $term .= $element . ' ';
                        continue;
                    }
                    $isPar = $idx % 2 == 0;
                    if ($isPar) {
                        $term .= $element . ' ';
                    } else {
                        $phrase .= '"' . $element . '" ';
                    }
                }
            }

            $term = trim($term);
            $phrase = trim($phrase);

            $helper = $query->getHelper();
            $searchTerm = $helper->escapeTerm($term);
            $searchPhrase = $helper->escapePhrase($phrase);
            $searchPhrase = str_replace('"', '', $searchPhrase);
            $query->setQuery($this->generateQuery($collection, $core, $searchTerm, $searchPhrase));
        }

        // make a new request, filtering for each facet
        $this->facetManager->setQueryByFacets($query, $facetsFilter, $core, $list3);

        //overwrite current fq to core specifics
        $coreHandler = new $classCore($query);
        $query = $coreHandler->queryCoreSpecifics($facetsFilter);
        $allDocuments = $client->select($query);
        $documentsFound = $allDocuments->getNumFound();

        $faceSetFound = $allDocuments->getFacetSet();
        $totalPages = ceil($documentsFound / $limit);
        $currentPageFrom = ($currentPage - 1) * $limit;

        /* Limit query by pagination limits */
        $query->setStart($currentPageFrom)->setRows($limit);
        $allDocuments = $client->execute($query);
        $documentsResponse = [];

        foreach ($allDocuments as $document) {
            $fields = $document->getFields();
            $fields["data"] = @json_decode($fields["data"]);

            $fields["data"]->description->entities_linked  = [];
            $fields["data"]->description->entities_non_linked = [];
            if (!isset($fields['data']->description->semantic_tags)) $fields['data']->description->semantic_tags = [];
            if (isset($fields['files'])) {
                foreach ($fields['files'] as $file) {
                    $last_uuid = substr($file, strrpos($file, "@",-4));
                    $last_uuid= str_replace("@", "", $last_uuid);

                    if (Storage::disk("semantic")->exists($fields["id"]."/".$last_uuid.".json")) {
                        $json = json_decode(Storage::disk("semantic")->get($fields["id"]."/".$last_uuid.".json"));
                        if(isset($json->xtags_interlinked)){
                            foreach ($json->xtags_interlinked as  $line) {
                                $line->uuid = $last_uuid;
                                $line->vocabulary = $json->vocabulary ?? "";
                            }
                            $fields["data"]->description->entities_linked = array_merge($fields["data"]->description->entities_linked, $json->xtags_interlinked);
                        }
                        if (isset($json->xtags)) {
                            foreach ($json->xtags as $line) {
                                $line->uuid = $last_uuid;
                                $line->vocabulary = $json->vocabulary ?? "";
                            }
                            $fields["data"]->description->entities_non_linked = array_merge($fields["data"]->description->entities_non_linked, $json->xtags);
                        }
                        if (isset($json->imageCaptionAi)) {
                            $fields["data"]->description->imageCaptionAi = $json->imageCaptionAi;
                        }
                    }
                }
                if (isset($fields['data']->description->semantic_tags)) {
                    foreach ($fields['data']->description->semantic_tags  as $key => $tag) {
                        $semanticData = $this->findObjectByName($fields["data"]->description->entities_linked, $tag);
                        if ($semanticData) {
                            $fields['data']->description->semantic_tags[$key] = $semanticData;
                        }
                    }
                }
                if (isset($fields['data']->description->semantic_tags)) {
                    foreach ($fields['data']->description->semantic_tags  as $key => $tag) {
                        $semanticData = $this->findObjectByName($fields["data"]->description->entities_non_linked, $tag);
                        if ($semanticData) {
                            $fields['data']->description->semantic_tags[$key] = $semanticData;
                        }
                     }

                }


            }

            $documentsResponse[] = $fields;
        }

        // the facets returned here are a complete unfiltered list, only the one that has been selected is marked as selected

        $facets = $this->stdToArray($this->facetManager->getFacets($faceSetFound, $facetsFilter, $core,   $list3));

        foreach ($facets as $key => $facet) {
            ksort($facets[$key]['values']);
        }
        $excludeKeys = ["created_at", "data", "core_resource_type", "collections", "core_resource_type_str", "files", "id", "name", "organization", "tags", "type", "updated_at", "attached_files", "previews", "tags_str", "types_str", "xeval_id", "catalog","exists","incrementing","preventsLazyLoading","score","usesUniqueIds","wasRecentlyCreated","timestamps"];
        $filteredFacetArray = [];
        $allKeys = array_column($facets, 'key');

        foreach ($facets as $obj) {
            $key = $obj['key'];
            if (!in_array("{$key}_str", $allKeys) && !in_array($key, $excludeKeys)) {
                $filteredFacetArray[] = $obj;
            }
        }

        return [
            'documentsFound'        => $documentsFound,
            'faceSetFound'          => $faceSetFound,
            'totalPages'            => $totalPages,
            'currentPageFrom'       => $currentPageFrom,
            'documentsResponse'     => $documentsResponse,
            'facets'                => $filteredFacetArray,
            'currentPage'           => $currentPage,
            'limit'                 => $limit,
            'nextPage'              => (($currentPage + 1) > $totalPages) ? $totalPages : $currentPage + 1,
            'prevPage'              => (($currentPage - 1) > 1) ? $currentPage - 1 : 1
        ];
    }

    private function findObjectByName($array, $name)
    {
        $result = array_filter($array, function ($e) use ($name) {
            return $e->name == $name;
        });
        return array_shift($result);
    }

    private function executeDistributedSearchQuery($pageParams = [], $sortParams = [], $facetsFilter, $workspace)
    {
        // Updates the facets filter
        $this->updateFacetsFilter($facetsFilter);

        // Gets the default core, with its client
        $defaultCore = null;
        foreach ($this->clients as $key => $value) if ($defaultCore === null) $defaultCore = $key;
        $client = $this->clients[$defaultCore];

        // Creates the select query
        $query = $client->createSelect();
        $distributedSearch = $query->getDistributedSearch();
        $i = 0;

        foreach ($this->clients as $key => $value) {
            if ($key !== $defaultCore) {
                $i++;
                $shardKey = 'shard' . $i;
                $shardValueParams = [
                    'host'  => $value->getEndpoints()['localhost']->getHost(),
                    'port'  => $value->getEndpoints()['localhost']->getPort(),
                    'path'  => $value->getEndpoints()['localhost']->getPath(),
                    'core'  => $value->getEndpoints()['localhost']->getCore()
                ];
                $shardValueParams['path'] = ($shardValueParams['path'] === '' ? '/' : $shardValueParams['path']);
                $shardValue = $shardValueParams['host'] . ':' . $shardValueParams['port'] . $shardValueParams['path'] . $shardValueParams['core'];
                $distributedSearch->addShard($shardKey, $shardValue);
            }
        }
        $resultset = $client->select($query);
        echo 'NumFound: ' . $resultset->getNumFound();
        exit();
        return [];
    }

    public static function stdToArray($std): array
    {
        return json_decode(json_encode($std), true);
    }


    private function addFacetType(&$facet, $solr_schema)
    {

        foreach ($facet as $key => $facet_element) {
            $facet_name = $facet_element['key'];

            if (!property_exists($solr_schema, $facet_name)) continue;

            switch ($solr_schema->$facet_name->type) {
                case 'boolean':
                    $type = 'boolean';
                    break;
                default:
                    $type = 'string';
                    break;
            }
            $facet[$key]['type'] = $type;
        }
    }
    private function generateQuery($collection, $core, $searchTerm, $searchPhrase): string
    {
        if ('document' === $core) {
            return DocumentSolrResource::generateQuery($searchTerm, $searchPhrase);
        } else if ('activity' === $core) {
            return ActivitySolrResource::generateQuery($searchTerm, $searchPhrase);
        } else if ('assessment' === $core) {
            return AssessmentSolrResource::generateQuery($searchTerm, $searchPhrase);
        } else if ('book' === $core) {
            return BookSolrResource::generateQuery($searchTerm, $searchPhrase);
        } else if ('course' === $core) {
            return CourseSolrResource::generateQuery($searchTerm, $searchPhrase);
        } else if ('multimedia' === $core) {
            return MultimediaSolrResource::generateQuery($searchTerm, $searchPhrase);
        }

        return "";
    }

    public function getCoreVersion($coreVersion)
    {
        return $this->solrConfig->getCoreVersion($coreVersion);
    }

    public function getCoreNameVersioned($solrCore, $solrVersion = null)
    {
        return $this->solrConfig->getCoreNameVersioned($solrCore, $solrVersion);
    }

    public function getClientCoreAlias()
    {
        return $this->solrConfig->getClientCoreAlias('lom');
    }

    public function handleSelect($params, $core)
    {
        $client = $this->getClient($core);
        $query = $client->createSelect();
        $request_params = array_merge([], $params);

        $request = $client->createRequest($query);
        $schemaConfig = config('solr_facets.client.' . env('APP_CLIENT', 'DEFAULT'));

        foreach ($params as $pKey => $param) {
            if (!is_array($param)) $param = [$param];
            $params_to_add = [];
            foreach ($param as $param_key => $param_value) {
                $value_search = strpos($param_value, ':');
                if (($pKey === 'q' || $pKey === 'qf') && $value_search === false) {
                    foreach ($client->getOption('schema') as $key_schema => $schema_value) {
                        $params_to_add[] = $key_schema . ":" . $param_value;
                    }
                    $param_value = $this->getJoin($core) . $param_value;
                } else {
                    foreach ($schemaConfig as $scKey => $value) {
                        $param_value = str_replace($value['solr_label'] . ':', $this->getJoin($core), $param_value);
                    }
                }
                $param[$param_key] = $param_value;
            }
            $param = array_merge($param, $params_to_add);
            if (count($param) === 1) $param = $param[0];
            $params[$pKey] = $param;
        }

        $request->setParams($params);
        $endpoint = $client->getEndpoint();
        $uri = AdapterHelper::buildUri($request, $endpoint);
        return $this->executeRequest($uri, $params['wt'] ?? 'json', $request_params, $core);
    }

    private function executeRequest($url, $wt, $changeParams, $core)
    {
        $request = new GuzzleHttpClient();
        $parsed_body = [];
        try {
            $res = $request->request('GET', "$url");
            header('Content-Type: ' . $res->getHeaderLine('Content-Type'));
            http_response_code($res->getStatusCode());
            $body = $res->getBody()->getContents();
            $parsed_body = $wt === 'json'
                ? $this->handleJSONResponse($body, $changeParams, $core)
                : $this->handleXMLResponse($body, $changeParams, $core);

            // echo $parsed_body;
        } catch (RequestException $exc) {
            $res = $exc->getResponse();
            header('Content-Type: ' . $res->getHeaderLine('Content-Type'));
            http_response_code($res->getStatusCode());
            $body = $res->getBody()->getContents();
            $parsed_body = $wt === 'json'
                ? $this->handleJSONResponseFail($body, $changeParams, $core)
                : $this->handleXMLResponseFail($body, $changeParams, $core);

            // echo $parsed_body;
        } finally {
            return $parsed_body;
        }
    }

    private function getJoin()
    {
        return "{!join from=dam_resource_id to=id fromIndex=" . self::LOM . "}lom_value:";
    }

    private function handleJSONResponseFail($txt, $changeParams, $core)
    {
        $json = json_decode($txt, true);
        $json['responseHeader']['params'] = $changeParams;

        return json_encode($json);
    }

    private function handleXMLResponseFail($txt, $changeParams, $core)
    {
        return $txt;
    }

    private function handleJSONResponse($txt, $changeParams, $core)
    {
        $json = json_decode($txt, true);
        $dam_ids = array_column($json['response']['docs'], 'id');
        $lom_items = $core == 'lom'
            ? Lom::whereIn('dam_resource_id', $dam_ids)->get()
            : Lomes::whereIn('dam_resource_id', $dam_ids)->get();

        // $cdnAlfresco = CDN::where('name', env('CDN_ALFRESCO_NAME', 'alfrescoCDN'));

        $lom_items_id = array_column($lom_items->toArray(), 'dam_resource_id');
        $hideFields = config('solr_facets.lom_hidden.' . env('APP_CLIENT', 'DEFAULT'));
        $schemaConfig = config('solr_facets.client.' . env('APP_CLIENT', 'DEFAULT'));
        // $changeParams = [];
        foreach ($json['response']['docs'] as $idx => $doc) {
            $indexLom = array_search($doc['id'], $lom_items_id);
            foreach ($schemaConfig as $key_schema => $value) {
                $json['response']['docs'][$idx][$value['solr_label']] = $indexLom !== false
                    ? $lom_items[$indexLom]->$key_schema
                    : null;
            }

            //* hide Ximdex fields
            foreach ($hideFields as $field) {
                unset($json['response']['docs'][$idx][$field]);
            }

            //* url hash
            if (isset($doc['data']['cdns_attached'])) {
                $hasLink = false;
                foreach ($doc['data']['cdns_attached'] as $index => $cdn_attached) {
                    if (!$hasLink && $cdn_attached['name'] === env('CDN_ALFRESCO_NAME', 'alfrescoCDN')) {
                        $json['response']['docs'][$idx]['url'] =  url(env('DAM_FRONT_URL', 'http://xdamv3.mhe.ximdex.net') . '/resource/' . $cdn_attached['hash'] . '/preview');
                        break;
                    }
                }
                unset($doc['data']['cdns_attached']);
            } else {
                $json['response']['docs'][$idx]['url'] = url(env('DAM_FRONT_URL', 'http://xdamv3.mhe.ximdex.net'));
            }
        }

        if (count($changeParams) > 0) {
            //* change params from request
            $json['responseHeader']['params'] = $changeParams;
        }

        return json_encode($json);
    }

    private function handleXMLResponse($txt, $changeParams, $core)
    {
        $dom = new DOMDocument();
        $dom->loadXML($txt);
        $hideFields = config('solr_facets.lom_hidden.' . env('APP_CLIENT', 'DEFAULT'));
        return $dom->saveXML();
    }


    public function documentExists($resource, $solrVersion = null)
    {
        $solrVersion = $this->getCoreVersion($solrVersion);
        $this->clients = $this->solrConfig->updateSolariumClients($solrVersion);

        $client = $this->getClientFromResource($resource);

        if ($client === null) {
            throw new Exception("Unable to get Solr client for the provided resource.");
        }

        $query = $client->createSelect();
        $query->setQuery("id:$resource->id");
        $resultset = $client->select($query);

        return $resultset->getNumFound() > 0;
    }
    public function getAllFieldsFromCore(string $core): array
    {
        try {
            $client = $this->getClient($core);

            $lukeQuery = $client->createLuke();
            $lukeQuery->setShow($lukeQuery::SHOW_ALL);

            $result = $client->luke($lukeQuery);

            $fields = $result->getFields();

            $fieldNames = array_keys($fields);

            $fieldNames = array_filter($fieldNames, function ($fieldName) {
                return !str_starts_with($fieldName, '_');
            });

            return [$core => array_values($fieldNames)];
        } catch (Exception $e) {
            Log::error("Error retrieving fields from core {$core}: " . $e->getMessage());
            return [];
        }
    }
}
