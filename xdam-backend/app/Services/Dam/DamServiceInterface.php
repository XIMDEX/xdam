<?php

namespace App\Services\Dam;

use TSterker\Solarium\SolariumManager;

interface DamServiceInterface
{
    /**
     * DamService constructor.
     * @param SolariumManager $solarium
     */
    public function __construct( SolariumManager $solarium );

    /**
     * Set current solr core to dam core
     */
    public function setCurrentCore();

    /**
     * Return a solarium instance
     */
    public function getSolarium();

    /**
     * Transform string with facets filters to array
     * @param $facetsFilter
     * @return mixed
     */
    public function transformFacetsFilter( $facetsFilter );

    /**
     * Remove document from dam core index
     * @param string $id
     */
    public function deleteDocumentById( string $id );

    /**
     * Limit query by facets and facets filters
     * @param $query
     * @param $facetsFilter
     */
    public function setQueryByFacets( $query, $facetsFilter );

    /**
     * Define facets from current query
     * @param $facetSet
     * @param $facetsFilter
     */
    public function setFacets( $facetSet, $facetsFilter );

    /**
     * Get facets returned from current query
     * @param $facetSet
     * @param $facetsFilter
     * @return array
     */
    public function getFacets( $facetSet, $facetsFilter );
}
