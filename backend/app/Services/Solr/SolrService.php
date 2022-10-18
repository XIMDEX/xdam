<?php


namespace App\Services\Solr;

use App\Models\Collection;
use App\Models\DamResource;
use App\Services\Catalogue\FacetManager;
use Exception;
use Solarium\Client;
use Solarium\Core\Query\Result\ResultInterface;
use stdClass;
use App\Http\Resources\Solr\ActivitySolrResource;
use App\Http\Resources\Solr\AssessmentSolrResource;
use App\Http\Resources\Solr\BookSolrResource;
use App\Http\Resources\Solr\CourseSolrResource;
use App\Http\Resources\Solr\MultimediaSolrResource;

/**
 * Class that is responsible for making crud with Apache Solr and each of its instances
 * Class SolrService
 * @package App\Services\Solr
 */
class SolrService
{

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
    }

    /**
     * returns the document that will be finally indexed in solr
     * @param DamResource $resource
     * @param $resourceClass
     * @return array
     */
    private function getDocumentFromResource(DamResource $resource, $resourceClass): array
    {
        return json_decode((new $resourceClass($resource))->toJson(), true);
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
            if (array_key_exists($connection, $this->clients)) {
                return $this->clients[$connection];
            } else {
                throw new Exception ("there is no client for the collection $collection->id");
            }
        } else {
            throw new Exception ("The collection $collection->id does not have a connection_name configured");
        }
    }

    /**
     * given a resource, returns the required solr client instance
     * @param DamResource $damResource
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

            if ($attempt < 20) {
                sleep(5);
                $client = $this->getClientFromResource($damResource, $attempt + 1);
            }
        }

        return $client;
    }


    public function getClient(string $client)
    {
        if(!array_key_exists($client, $this->clients)) {
            throw new Exception("There is no client ${client}");
        }

        return $this->clients[$client];
    }

    /**
     * update or save a document in solr
     * @param DamResource $damResource
     * @param string $solrVersion
     * @return ResultInterface
     * @throws Exception
     */
    public function saveOrUpdateDocument(DamResource $damResource, $solrVersion = null): ResultInterface
    {
        $solrVersion = $this->getCoreVersion($solrVersion);
        $this->clients = $this->solrConfig->updateSolariumClients($solrVersion);
        $client = $this->getClientFromResource($damResource);
        $createCommand = $client->createUpdate();
        $document = $createCommand->createDocument();

        $documentResource = $this->getDocumentFromResource($damResource, $client->getOption('resource'));

        foreach ($documentResource as $key => $value) {
            $document->$key = $value;
        }

        $createCommand->addDocument($document);
        $createCommand->addCommit();
        return $client->update($createCommand);
    }

    /**
     * Delete a document in Solr
     * @param DamResource $damResource
     * @return ResultInterface
     * @throws Exception
     */
    public function deleteDocument(DamResource $damResource): ResultInterface
    {
        $client = $this->getClientFromResource($damResource);
        $deleteQuery = $client->createUpdate();
        $deleteQuery->addDeleteQuery('id:' . $damResource->id);
        $deleteQuery->addCommit();
        return $client->update($deleteQuery);
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

    private static function updateFacetsFilter(&$facetsFilter)
    {
        //we need to replace all strings with spaces to  "\ " otherwise, Solr don't recognize it.
        foreach ($facetsFilter as $key => $value) {
            if(is_string($value)) {
                $facetsFilter[$key] = str_replace(" ", "\ ", $value);
            }
            if(is_array($value)) {
                foreach ($value as $k => $v) {
                    if(is_string($v)) {
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

        /* the facets to be applied to the query  */
        $this->facetManager->setFacets($facetSet, [], $core);
        /*  limit the query to facets that the user has marked us */
        $this->facetManager->setQueryByFacets($query, [], $core);

        /* if we have a search param, restrict the query */
        if (!empty($search)) {
            $helper = $query->getHelper();
            $searchTerm = $helper->escapeTerm($search);
            $searchPhrase = $helper->escapePhrase($search);
            $query->setQuery($this->generateQuery($collection, $core, $searchTerm));
            //$query->setQuery("name:$searchTerm OR data:*$searchPhrase* OR achievements:*$searchPhrase* OR preparations:*$searchPhrase*");
            /*if ('document' === $core) {
                $query->setQuery("title:$searchTerm^10 title:*$searchTerm*^7 OR body:*$searchTerm*^5");
            } else {
                $query->setQuery("name:$searchTerm^10 name:*$searchTerm*^7 OR data:*$searchTerm*^5 achievements:*$searchTerm*^3 OR preparations:*$searchTerm*^3");
            }*/
        }

        // the query is done without the facet filter, so that it returns the complete list of facets and the counter present in the entire index
        // $allDocuments = $client->select($query);
        // $faceSetFound = $allDocuments->getFacetSet();

        // make a new request, filtering for each facet
        $this->facetManager->setQueryByFacets($query, $facetsFilter, $core);
        
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
            $documentsResponse[] = $fields;
        }

        // the facets returned here are a complete unfiltered list, only the one that has been selected is marked as selected
        $facets = $this->stdToArray($this->facetManager->getFacets($faceSetFound, $facetsFilter, $core));
        
        foreach ($facets as $key => $facet) {
            ksort($facets[$key]['values']);
        }

        return [
            'documentsFound'        => $documentsFound,
            'faceSetFound'          => $faceSetFound,
            'totalPages'            => $totalPages,
            'currentPageFrom'       => $currentPageFrom,
            'documentsResponse'    => $documentsResponse,
            'facets'                => $facets,
            'currentPage'           => $currentPage,
            'limit'                 => $limit,
            'nextPage'              => (($currentPage + 1) > $totalPages) ? $totalPages : $currentPage + 1,
            'prevPage'              => (($currentPage - 1) > 1) ? $currentPage - 1 : 1
        ];
    }

    public static function stdToArray($std): array
    {
        return json_decode(json_encode($std), true);
    }

    private function generateQuery($collection, $core, $searchTerm): string
    {
        if ('document' === $core) {
            return "title:$searchTerm^10 title:*$searchTerm*^7 OR body:*$searchTerm*^5";
        } else if ('activity' === $core) {
            return ActivitySolrResource::generateQuery($searchTerm);
        } else if ('assessment' === $core) {
            return AssessmentSolrResource::generateQuery($searchTerm);
        } else if ('book' === $core) {
            return BookSolrResource::generateQuery($searchTerm);
        } else if ('course' === $core) {
            return CourseSolrResource::generateQuery($searchTerm);
        } else if ('multimedia' === $core) {
            return MultimediaSolrResource::generateQuery($searchTerm);
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
}
