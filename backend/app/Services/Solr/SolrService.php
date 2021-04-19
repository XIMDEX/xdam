<?php


namespace App\Services\Solr;

use App\Models\Collection;
use App\Models\DamResource;
use App\Services\Catalogue\FacetManager;
use Exception;
use Solarium\Core\Query\Result\ResultInterface;
use stdClass;

/**
 * Class that is responsible for making crud with Apache Solr and each of its instances
 * Class SolrService
 * @package App\Services\Solr
 */
class SolrService
{

    private FacetManager $facetManager;
    private array $clients;

    /**
     * SolrService constructor.
     * @param FacetManager $facetManager
     * @param SolrConfig $solrConfig
     */
    public function __construct(FacetManager $facetManager, SolrConfig $solrConfig)
    {
        $this->facetManager = $facetManager;
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
     * @return mixed
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
    public function getClientFromResource(DamResource $damResource)
    {
        return $this->getClientFromCollection($damResource->collection);
    }

    /**
     * update or save a document in solr
     * @param DamResource $damResource
     * @return ResultInterface
     * @throws Exception
     */
    public function saveOrUpdateDocument(DamResource $damResource): ResultInterface
    {
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
        $client = $this->getClientFromCollection($collection);
        $core = $collection->accept;
        $search = $pageParams['search'];
        $currentPage = $pageParams['currentPage'];
        $limit = $pageParams['limit'];

        $query = $client->createSelect();
        $facetSet = $query->getFacetSet();

        /* the facets to be applied to the query  */
        $this->facetManager->setFacets($facetSet, [], $core);
        /*  limit the query to facets that the user has marked us */
        $this->facetManager->setQueryByFacets($query, []);

        /* if we have a search param, restrict the query */
        if (!empty($search)) {
            $query->setQuery('data:*' . $search . "* OR name:*$search*");
        }

        // the query is done without the facet filter, so that it returns the complete list of facets and the counter present in the entire index
        // $allDocuments = $client->select($query);
        // $faceSetFound = $allDocuments->getFacetSet();

        // make a new request, filtering for each facet
        $this->facetManager->setQueryByFacets($query, $facetsFilter);
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

        /* Response with pagination data */
        $response = new \stdClass();

        // the facets returned here are a complete unfiltered list, only the one that has been selected is marked as selected
        $facets = $this->stdToArray($this->facetManager->getFacets($faceSetFound, $facetsFilter, $core));
        foreach ($facets as $key => $facet) {
            ksort($facets[$key]['values']);
        }
        $response->facets = $facets;
        $response->current_page = $currentPage;
        $response->data = $documentsResponse;
        $response->per_page = $limit;
        $response->last_page = $totalPages;
        $response->next_page = (($currentPage + 1) > $totalPages) ? $totalPages : $currentPage + 1;
        $response->prev_page = (($currentPage - 1) > 1) ? $currentPage - 1 : 1;
        $response->total = $documentsFound;
        return $response;
    }

    public static function stdToArray($std): array
    {
        return json_decode(json_encode($std), true);
    }

}
