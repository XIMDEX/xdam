<?php

namespace App\Services\Catalogue;

class CoreFacetsBuilder {
    private $coreList;
    private $and_facets = []; //add here facets with AND operator behaviour. Example ['tags']; The default operator is OR
    private $formedList;


    public function __construct()
    {
        $this->coreList = config('solarium.facets');
    }

    public function upCoreConfig(): array
    {

        foreach ($this->coreList as $coreName => $facets) {
            foreach ($facets as $facet) {

                $this->formedList[$coreName][$facet] = [
                    "name" => $facet,
                    "operator" => in_array($facet, $this->and_facets) ? 'AND' : 'OR'
                ];
            }
        }

        return $this->formedList;
    }
}
