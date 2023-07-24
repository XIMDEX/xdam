<?php

namespace App\Services\Catalogue;

use App\Models\Collection;
use App\Utils\Texts;
use Solarium\QueryType\Select\Query\Query;
use App\Services\Solr\SolrConfig;


class FacetManager
{
    //Convert to dynamic list based on input schema. This is what is going to display in front facets
    private $facetList = [];
    // "name to display" => "name faceted"
    private $facetLists;
    const UNLIMITED_FACETS_VALUES = -1;
    const RADIO_FACETS = ['active', 'aggregated', 'internal', 'internal', 'external', 'isFree', 'is_deleted'];

    /**
     * @var SolrConfig
     */
    private SolrConfig $solrConfig;

    public function __construct(CoreFacetsBuilder $coreFacetsBuilder, SolrConfig $solrConfig)
    {
        $this->facetLists = $coreFacetsBuilder->upCoreConfig();
        $this->solrConfig = $solrConfig;
    }

    //Define black-list fields (organization_id)

    /**
     * Limit query by facets and facets filters
     * @param Query $query
     * @param array $facetsFilter
     */
    public function setQueryByFacets($query, $facetsFilter, $core)
    {
        if (!empty($facetsFilter)) {
            foreach ($facetsFilter as $filterName => $filterValue) {
                // The filter value can be single or an array
                if (is_array($filterValue)) {
                    $q = '';
                    $operator = $this->facetLists[$core][$filterName]['operator'];
                    foreach ($filterValue as $key => $id) {
                        $q .= $key == 0 ? "$filterName:$id" : " $operator $filterName : $id";
                    }
                    //$q .= ' AND organization:'. $oid;
                    $query->createFilterQuery($filterName)->setQuery($q);
                } else {
                    $q = $filterName . ':' . $filterValue;
                    //$q .= ' AND organization:'. $oid;
                    $query->createFilterQuery($filterName)->setQuery($q);
                }
            }
        }
    }

    /**
     * Transform string with facets filters to array
     * @param array $facetsFilter
     * @return mixed
     */
    public function transformFacetsFilter($facetsFilter)
    {
        if (!empty($facetsFilter)) {
            foreach ($facetsFilter as $index => $facetFilter) {
                if (strpos($facetFilter, ',') !== false) {
                    $facetFilter[$index] = explode(',', $facetFilter);
                }
            }
        }
        return $facetsFilter;
    }

    /**
     * Define facets from current query
     * @param \Solarium\Component\FacetSet $facetSet
     * @param array $facetsFilter
     */
    public function setFacets($facetSet, $facetsFilter, $core)
    {
        $this->facetList = $this->facetLists[$core];
        foreach ($this->facetList as $key => $value) {
            $facetSet
                ->createFacetField($value['name'])
                ->setField($value['name'])
                ->setLimit(self::UNLIMITED_FACETS_VALUES);

            if (!empty($facetsFilter)) {
                foreach ($facetsFilter as $keyFilter => $valueFilter) {
                    if ($keyFilter == $value['name']) {
                        $facetSet->createFacetQuery($valueFilter)->setQuery($keyFilter . ": " . $valueFilter);
                    }
                }
            }
        }
    }

    /**
     * Get facets returned from current query
     * @param \Solarium\Component\FacetSet $facetSet
     * @param array $facetsFilter
     * @return array
     */
    public function getFacets($facetSet, $facetsFilter, $core): array
    {
        $this->facetList = $this->facetLists[$core];
        $facetsArray = [];
        // go through each of the facets list
        foreach ($this->facetList as $facetLabel => $facetKey) {
            $facetItem = null;
            $facet = $facetSet->getFacet($facetKey['name']);
            $values = $facet->getValues();
            $isBoolean = false;
            $collection = Collection::find($facetsFilter['collections']);

         //   if($facetsFilter['collections'] === 49)$collection = Collection::find($facetsFilter['collection']);

            if (in_array($facetKey['name'], self::RADIO_FACETS) && (key_exists('true', $values) || key_exists('false', $values))) {
                $isBoolean = true;
            }

            if ($facet) {
                $property = new \stdClass();
                // iterates through each faceted collection
                foreach ($facet as $valueFaceSet => $count) {
                        $facetItem = new \stdClass();
                        $facetItem->key = $facetKey['name'];
                        $facetItem->label = Texts::facets($facetLabel);
                        $isSelected = false;

                        // if it exists in the parameter filter, mark it as selected
                        if (array_key_exists($facetKey['name'], $facetsFilter)) {
                            if (is_array($facetsFilter[$facetKey['name']])) {
                                foreach ($facetsFilter[$facetKey['name']] as $filterValue) {
                                    if ($filterValue === $valueFaceSet) {
                                        $isSelected = true;
                                    }
                                }
                            } else {
                                if ($facetsFilter[$facetKey['name']] === $valueFaceSet) {
                                    $isSelected = true;
                                }
                            }
                        }

                        if ($isBoolean) {
                            $valueFaceSet = Texts::facets($valueFaceSet);
                        }

                        // return the occurrence count and if it is selected or not
                        $property->$valueFaceSet = ["count" => $count, "selected" => $isSelected, "radio" => in_array($facetKey['name'], self::RADIO_FACETS)];
                        //$property->$valueFaceSet = ["count" => $count, "selected" => $isSelected];
                        $facetItem->values = $property;
                    if ($isBoolean && count($values) == 2) {
                        $all_count = $values['false'] + $values['true'];
                        $facetItem->values->{Texts::facets('all')} = ['count' => $all_count, 'selected' => true, 'radio' => true];
                    }

                    if ($collection) {
                        $type = ucfirst($this->solrConfig->getNameCoreConfig($collection->solr_connection));
                        if (class_exists("App\\Services\\{$type}Service")){
                            $service = app("App\\Services\\{$type}Service");
                            $facetItem->values = $service::handleFacetValues($facetItem->values, $facetItem->key);
                        } 
                    }
                }
                if ($facetItem != null) {
                    $facetsArray[] = $facetItem;
                }
            }
        }

        return $facetsArray;
    }
}
