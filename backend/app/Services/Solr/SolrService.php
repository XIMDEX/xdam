<?php


namespace App\Services\Solr;


use App\Enums\CollectionType;
use App\Services\Catalogue\FacetManager;
use Exception;
use Solarium\Core\Query\Result\ResultInterface;
use stdClass;
use TSterker\Solarium\SolariumManager;

class SolrService
{
    protected array $collections = [
        CollectionType::course => "xdam-course",
        CollectionType::multimedia => "xdam-multimedia"
    ];

    /**
     * @var SolariumManager
     */
    private SolariumManager $solarium;
    /**
     * @var FacetManager
     */
    private FacetManager $facetManager;

    /**
     * @var SolrConfigService
     */
    private SolrConfigService $solrConfig;

    /**
     * SolrService constructor.
     * @param SolariumManager $solarium
     * @param FacetManager $facetManager
     * @param SolrConfigService $solrConfig
     */
    public function __construct(SolariumManager $solarium, FacetManager $facetManager, SolrConfigService $solrConfig)
    {
        $this->solarium = $solarium;
        $this->facetManager = $facetManager;
        $this->solrConfig = $solrConfig;
        $this->solrConfig->config($this->solarium, $this->collections);
        $this->setDefaultCollection();
    }

    public function solrServerIsReady(): bool
    {
        foreach ($this->collections as $core) {
            if (!$this->solrConfig->checkCoreAlreadyExists($core)) {
                $response = $this->solrConfig->createSolrCore($core);
                if ($response)
                {
                    throw new Exception("Failed to create Solr Cores");
                }
            }
            $diffFields = $this->solrConfig->getSchemaDifferences($core);
            if (!empty($diffFields)) {
                $this->solrConfig->createSchemaForCore($core, $diffFields);
            }
        }
        return true;
    }

    public function getCollectionBySubType(string $subType)
    {
        if ($subType == CollectionType::course) {
            return CollectionType::course;
        } else {
            return CollectionType::multimedia;
        }
    }

    public function isValidCollection(string $collection)
    {
        if (array_key_exists($collection, $this->collections)) {
            return true;
        }
        return false;
    }

    public function setDefaultCollection()
    {
        $this->setCollection(CollectionType::multimedia);
    }

    public function setCollection(string $collection)
    {
        if ($this->isValidCollection($collection)) {
           $this->solarium->getEndpoint()->setCollection($this->collections[$collection]);
        }
    }

    public function ping(): bool
    {
        // create a ping query
        $ping = $this->solarium->createPing();

        // execute the ping query
        try {
            $this->solarium->ping($ping);
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getDocumentById(string $id): array
    {
        $select = $this->solarium->createRealtimeGet();
        $select->addId($id);
        $result = $this->solarium->realtimeGet($select);
        $document = [];

        foreach ($result->getDocument() as $field => $value) {
            $document[ucfirst($field)] = $value;
        }

        return $document;
    }

    public function saveOrUpdateDocument($data): ResultInterface
    {
        $this->setCollection($data->collection);
        $createCommand = $this->solarium->createUpdate();
        $document = $createCommand->createDocument();

        foreach ($data as $key => $value) {
            $document->$key = $value;
        }

        $createCommand->addDocument($document);
        $createCommand->addCommit();
        return $this->solarium->update($createCommand);
    }

    public function deleteDocumentById(string $id, string $collection): ResultInterface
    {
        $this->setCollection($collection);
        $deleteQuery = $this->solarium->createUpdate();
        $deleteQuery->addDeleteQuery('id:' . $id);
        $deleteQuery->addCommit();
        return $this->solarium->update($deleteQuery);
    }

    public function cleanSolr()
    {
        foreach($this->collections as $collection)
        {
            $this->solarium->getEndpoint()->setCollection($collection);

            // get an update query instance
            $update = $this->solarium->createUpdate();

            // add the delete query and a commit command to the update query
            $update->addDeleteQuery('*:*');
            $update->addCommit();


            // this executes the query and returns the result
            $this->solarium->update($update);
        }

    }


    public function queryByFacet($facetsFilter, $collection): stdClass
    {
        $this->setCollection($collection);
        $query = $this->solarium->createSelect();

        $facetSet = $query->getFacetSet();

        /* the facets to be applied to the query  */
        $this->facetManager->setFacets($facetSet, $facetsFilter);
        /*  limit the query to facets that the user has marked us */
        $this->facetManager->setQueryByFacets($query, $facetsFilter);


        $allDocuments = $this->solarium->select($query);
        $facets = $allDocuments->getFacetSet();

        $result = new stdClass();

        if (!empty($facets)) {
            $result->data = [];

            foreach ($allDocuments as $document) {
                $result->data[] = $document->getFields();
            }
        }

        return $result;
    }

    public function paginatedQueryByFacet(
        $pageParams = [],
        $sortParams = [],
        $facetsFilter,
        $collection
    ): stdClass {
        $this->setCollection($collection);

        $search = $pageParams['search'];
        $currentPage = $pageParams['currentPage'];
        $limit = $pageParams['limit'];

        $query = $this->solarium->createSelect();

        $facetSet = $query->getFacetSet();

        /* the facets to be applied to the query  */
        $this->facetManager->setFacets($facetSet, []);
        /*  limit the query to facets that the user has marked us */
        $this->facetManager->setQueryByFacets($query, []);

        /* if we have a search param, restrict the query */
        if (!empty($search)) {
            $query->setQuery("data:*" . $search . "*");
        }

        // the query is done without the facet filter, so that it returns the complete list of facets and the counter present in the entire index
        $allDocuments = $this->solarium->select($query);
        $faceSetFound = $allDocuments->getFacetSet();

        // make a new request, filtering for each facet
        $this->facetManager->setQueryByFacets($query, $facetsFilter);
        $allDocuments = $this->solarium->select($query);
        $documentsFound = $allDocuments->getNumFound();

        $totalPages = ceil($documentsFound / $limit);
        $currentPageFrom = ($currentPage - 1) * $limit;

        /* Limit query by pagination limits */
        $query->setStart($currentPageFrom)->setRows($limit);

        $allDocuments = $this->solarium->execute($query);

        $documentsResponse = [];

        foreach ($allDocuments as $document) {
            $fields = $document->getFields();
            $fields["data"] = @json_decode($fields["data"]);
            $documentsResponse[] = $fields;
        }

        /* Response with pagination data */
        $response = new \stdClass();

        // the facets returned here are a complete unfiltered list, only the one that has been selected is marked as selected
        $response->facets = $this->facetManager->getFacets($faceSetFound, $facetsFilter);
        $response->current_page = $currentPage;
        $response->data = $documentsResponse;
        $response->per_page = $limit;
        $response->last_page = $totalPages;
        $response->next_page = (($currentPage + 1) > $totalPages) ? $totalPages : $currentPage + 1;
        $response->prev_page = (($currentPage - 1) > 1) ? $currentPage - 1 : 1;
        $response->total = $documentsFound;
        return $response;
    }

}
