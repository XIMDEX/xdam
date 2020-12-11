<?php


namespace App\Services;


use App\Services\Catalogue\FacetManager;
use TSterker\Solarium\SolariumManager;

class SolrService
{
    protected $collection = 'xdam';
    /**
     * @var SolariumManager
     */
    private $solarium;
    /**
     * @var FacetManager
     */
    private $facetManager;

    /**
     * SolrService constructor.
     * @param SolariumManager $solarium
     * @param FacetManager $facet
     */
    public function __construct(SolariumManager $solarium, FacetManager $facetManager)
    {
        $solarium->getEndpoint()->setCollection($this->collection);
        $this->solarium = $solarium;
        $this->facetManager = $facetManager;
    }

    public function ping()
    {
        // create a ping query
        $ping = $this->solarium->createPing();

        // execute the ping query
        try {
            $this->solarium->ping($ping);
            return true;
        } catch (\Solarium\Exception $e) {
            return false;
        }
    }

    public function getDocumentById(string $id)
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

    public function saveOrUpdateDocument($data)
    {
        $createCommand = $this->solarium->createUpdate();
        $document = $createCommand->createDocument();


        foreach ($data as $key => $value) {
            $document->$key = $value;
        }

        $createCommand->addDocument($document);
        $createCommand->addCommit();
        return $this->solarium->update($createCommand);
    }

    public function deleteDocumentById(string $id)
    {
        $deleteQuery = $this->solarium->createUpdate();
        $deleteQuery->addDeleteQuery('id:' . $id);
        $deleteQuery->addCommit();
        return $this->solarium->update($deleteQuery);
    }

    public function cleanSolr()
    {
        // get an update query instance
        $update = $this->solarium->createUpdate();

        // add the delete query and a commit command to the update query
        $update->addDeleteQuery('*:*');
        $update->addCommit();

        // this executes the query and returns the result
        return $this->solarium->update($update);
    }


    public function queryByFacet($facetsFilter)
    {

        $query = $this->solarium->createSelect();

        $facetSet = $query->getFacetSet();

        /* the facets to be applied to the query  */
        $this->facetManager->setFacets($facetSet, $facetsFilter);
        /*  limit the query to facets that the user has marked us */
        $this->facetManager->setQueryByFacets($query, $facetsFilter);


        $allDocuments = $this->solarium->select($query);
        $facets = $allDocuments->getFacetSet();

        if (empty($facets))
        {
            return [];
        }

        $result = new \stdClass();
        $result->data = [];

        foreach ($allDocuments as $document) {
                $result->data[]= $document->getFields();
        }
        return $result;

    }

    public function paginatedQueryByFacet($pageParams = [], $sortParams = [], $facetsFilter = nu)
    {
        $search = $pageParams['search'];
        $currentPage = $pageParams['currentPage'];
        $limit = $pageParams['limit'];

        $query = $this->solarium->createSelect();

        $facetSet = $query->getFacetSet();

        /* the facets to be applied to the query  */
        $this->facetManager->setFacets($facetSet, $facetsFilter);
        /*  limit the query to facets that the user has marked us */
        $this->facetManager->setQueryByFacets($query, $facetsFilter);

        /* if we have a search param, restrict the query */
        if (!empty($search)) {
            $query->setQuery("name:*" . $search . "*");
        }

        $allDocuments = $this->solarium->select($query);
        $documentsFound = $allDocuments->getNumFound();
        $faceSetFound = $allDocuments->getFacetSet();

        $totalPages = ceil($documentsFound / $limit);
        $currentPageFrom = ($currentPage - 1) * $limit;

        /* Limit query by pagination limits */
        $query->setStart($currentPageFrom)->setRows($limit);

        $allDocuments = $this->solarium->execute($query);

        $documentsResponse = [];

        foreach ($allDocuments as $document) {
            $documentsResponse[] = $document->getFields();
        }

        /* Response with pagination data */
        $response = new \stdClass();
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
