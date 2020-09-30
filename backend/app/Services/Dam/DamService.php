<?php


namespace App\Services\Dam;

use TSterker\Solarium\SolariumManager;

class DamService implements DamServiceInterface
{
    /**
     * @var SolariumManager
     */
    private $solarium;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $crawlerCore;

    private $facetList = [
        "Propietario" => "owner",
        "Tipo Mime" => "mime_type",
        "Tipo" => "type",
        "Extensión" => "extension",
    ];

    /**
     * DamService constructor.
     * @param SolariumManager $solarium
     */
    public function __construct(SolariumManager $solarium)
    {
        $this->solarium = $solarium;
    }

    /**
     * Set current solr core to dam core
     */
    public function setCurrentCore(){
        $this->crawlerCore = config('app.solr_core_dam', 'dam');
        if($this->solarium->getEndpoint()->getCore() != $this->crawlerCore) {
            $this->solarium->getEndpoint()->setCore($this->crawlerCore);
        }
    }

    /**
     * Return a solarium instance
     */
    public function getSolarium(){
        $this->setCurrentCore();
        return $this->solarium;
    }

    /**
     * Transform string with facets filters to array
     * @param $facetsFilter
     * @return mixed
     */
    public function transformFacetsFilter( $facetsFilter){
        if (!empty($facetsFilter)) {
            foreach($facetsFilter as $index=>$facetFilter){
                if (strpos($facetFilter, ',') !== false) {
                    $facetFilter[$index] = explode(',', $facetFilter);
                }
            }
        }
        return $facetsFilter;
    }

    /**
     * Remove document from dam core index
     * @param string $id
     */
    public function deleteDocumentById(string $id){
        $this->setCurrentCore();
        $deleteQuery = $this->solarium->createUpdate();
        $deleteQuery->addDeleteQuery('id:' . $id);
        $deleteQuery->addCommit();
        $this->solarium->update($deleteQuery);
    }

    /**
     * Limit query by facets and facets filters
     * @param $query
     * @param $facetsFilter
     */
    public function setQueryByFacets($query, $facetsFilter) {
        if (!empty($facetsFilter)) {
            foreach ( $facetsFilter as $filterName => $filterValue ) {
                $query->createFilterQuery( $filterValue )->setQuery( $filterName . ":" . $filterValue );
            }
        }
    }

    /**
     * Define facets from current query
     * @param $facetSet
     * @param $facetsFilter
     */
    public function setFacets($facetSet, $facetsFilter) {
        foreach ($this->facetList as $key => $value){
            $facetSet->createFacetField($value)->setField($value);
            if (!empty($facetsFilter)){
                foreach($facetsFilter as $keyFilter => $valueFilter) {
                    if ($keyFilter == $value) {
                        $facetSet->createFacetQuery($valueFilter)->setQuery( $keyFilter . ": " . $valueFilter);
                    }
                }
            }
        }
    }

    /**
     * Get facets returned from current query
     * @param $facetSet
     * @param $facetsFilter
     * @return array
     */
    public function getFacets($facetSet, $facetsFilter) {

        $facetsFilter = $this->transformFacetsFilter($facetsFilter);

        $facetsArray = [];

        foreach ($this->facetList as $key => $value) {
            $facetItem = new \stdClass();
            $facetItem->key = $value;
            $facetItem->label = $key;
            $facet = $facetSet->getFacet($value);

            if($facet) {
                $property  = new \stdClass();
                foreach ($facet as $valueFaceSet => $count) {
                    $foundInFilter = false;
                    if (!empty($facetsFilter)){
                        foreach ($facetsFilter as $filterName => $filterValue ) {
                            if ($filterName == $value) {
                                $facet = $facetSet->getFacet($filterValue);
                                if($facet) {
                                    $property->$filterValue = $facet->getValue();
                                    $facetItem->values = $property;
                                    $foundInFilter = true;
                                }
                            }
                        }
                    }

                    if (!$foundInFilter) {
                        $property->$valueFaceSet = $count;
                        $facetItem->values = $property;
                    }

                }
                $facetsArray[] = $facetItem;
            }

        }
        return $facetsArray;
    }


}
