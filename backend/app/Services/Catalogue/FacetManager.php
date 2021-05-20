<?php


namespace App\Services\Catalogue;


class FacetManager
{

    //Convert to dynamic list based on input schema. This is what is going to display in front facets
    private $facetList = [];
    private $radioValues = [];
    // "name to display" => "name faceted"
    private $facetLists = [
        "course" => [
            "categories" => "categories",
            "active" => "active",
            "tags" => "tags",
            "internal" => "internal",
            "aggregated" => "aggregated",
            "workspaces" => "workspaces"
        ],
        "multimedia" => [
            "categories" => "categories",
            "active" => "active",
            "type" => "type",
            "attached files" => "types",
            "tags" => "tags",
            "workspaces" => "workspaces"
        ],
        "activity" => [
            "categories" => "categories",
            "active" => "active",
            "workspaces" => "workspaces"
        ],
        "assessment" => [
            "categories" => "categories",
            "active" => "active",
            "workspaces" => "workspaces"
        ],
        "book" => [
            "categories" => "categories",
            "active" => "active",
            "tags" => "tags",
            "workspaces" => "workspaces"
        ]
    ];

    //Define black-list fields (organization_id)


    /**
     * Limit query by facets and facets filters
     * @param $query
     * @param $facetsFilter
     */
    public function setQueryByFacets($query, $facetsFilter)
    {
        if (!empty($facetsFilter)) {
            foreach ($facetsFilter as $filterName => $filterValue) {
                // The filter value can be single or an array
                if (is_array($filterValue)) {
                    $q = '';
                    foreach ($filterValue as $key => $id) {
                        $q .= $key == 0 ? $filterName . ':' . $id : ' OR ' . $filterName . ':' . $id;
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
    public function setFacets($facetSet, $facetsFilter, $core)
    {
        $this->facetList = $this->facetLists[$core];
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
    public function getFacets($facetSet, $facetsFilter, $core)
    {
        $this->facetList = $this->facetLists[$core];
        $facetsArray = [];
        // go through each of the facets list
        foreach ($this->facetList as $facetLabel => $facetKey) {
            $facetItem = null;
            $facet = $facetSet->getFacet($facetKey);

            if ($facet) {
                $property = new \stdClass();
                // iterates through each faceted collection
                foreach ($facet as $valueFaceSet => $count) {
                    // if ($count > 0) {
                        $facetItem = new \stdClass();
                        $facetItem->key = $facetKey;
                        $facetItem->label = $facetLabel;
                        $isSelected = false;
                        // if it exists in the parameter filter, mark it as selected
                        if (array_key_exists($facetKey, $facetsFilter)) {
                            if (is_array($facetsFilter[$facetKey])) {
                                foreach ($facetsFilter[$facetKey] as $filterValue) {
                                    if ($filterValue === $valueFaceSet) {
                                        $isSelected = true;
                                    }
                                }
                            } else {
                                if ($facetsFilter[$facetKey] === $valueFaceSet)
                                {
                                    $isSelected = true;
                                }
                            }
                        }
                        // return the occurrence count and if it is selected or nots
                        $property->$valueFaceSet = ["count" => $count, "selected" => $isSelected, "radio" => in_array($facetKey, $this->radioValues)];
                        //$property->$valueFaceSet = ["count" => $count, "selected" => $isSelected];
                        $facetItem->values = $property;
                    // }
                }
                if ($facetItem != null) {
                    $facetsArray[] = $facetItem;
                }
            }
        }
        return $facetsArray;
    }
}
