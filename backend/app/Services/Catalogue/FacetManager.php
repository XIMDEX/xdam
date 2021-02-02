<?php


namespace App\Services\Catalogue;


class FacetManager
{

    private $facetList = [
        "categories" => "categories",
        "active" => "active",
        "type" => "type"
    ];

    /**
     * Limit query by facets and facets filters
     * @param $query
     * @param $facetsFilter
     */
    public function setQueryByFacets($query, $facetsFilter)
    {
        if (!empty($facetsFilter)) {
            foreach ($facetsFilter as $filterName => $filterValue) {
                $query->createFilterQuery($filterValue)->setQuery($filterName . ":"  . $filterValue);
            }
        }
    }

    /**
     * Transform string with facets filters to array
     * @param $facetsFilter
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
     * @param $facetSet
     * @param $facetsFilter
     */
    public function setFacets($facetSet, $facetsFilter)
    {
        foreach ($this->facetList as $key => $value) {
            $facetSet->createFacetField($value)->setField($value);
            if (!empty($facetsFilter)) {
                foreach ($facetsFilter as $keyFilter => $valueFilter) {
                    if ($keyFilter == $value) {
                        $facetSet->createFacetQuery($valueFilter)->setQuery($keyFilter . ": " . $valueFilter);
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
    public function getFacets($facetSet, $facetsFilter)
    {
        $facetsArray = [];
        foreach ($this->facetList as $key => $value) {
            $facetItem = new \stdClass();
            $facetItem->key = $value;
            $facetItem->label = $key;
            $facet = $facetSet->getFacet($value);
            if ($facet) {
                $property = new \stdClass();
                foreach ($facet as $valueFaceSet => $count) {
                        $property->$valueFaceSet = $count;
                        $facetItem->values = $property;
                }
                $facetsArray[] = $facetItem;
            }
        }
        return $facetsArray;
    }
}
