<?php

namespace App\Services\Catalogue;

class CoreFacetsBuilder {
    private $coreList;
    private $and_facets = []; //add here facets with AND operator behaviour. Example ['tags']; The default operator is OR
    private $formedList;

    public function __construct()
    {
        $this->coreList = [
            "course" => [
                "categories",
                "active",
                "workspaces",
                "tags",
                "internal",
                "aggregated",
                "duration",
                "isFree",
                "currency",
                "cost",
                "skills",
                "lom",
                "lomes"
            ],
            "multimedia" => [
                "categories",
                "active",
                "type",
                "tags",
                "workspaces",
                "lom",
                "lomes"
            ],
            "document" => [
                "categories",
                "active",
                "type",
                "types",
                "tags",
                "workspaces",
                "lom",
                "lomes"
            ],
            "activity" => [
                "categories",
                "active",
                "workspaces",
                "lom",
                "lomes"
            ],
            "assessment" => [
                "categories",
                "active",
                "workspaces",
                "lom",
                "lomes"
            ],
            "book" => [
                "categories",
                "active",
                "tags",
                "workspaces",
                "isbn",
                "units",
                "lang",
                "lom",
                "lomes"
            ]
        ];
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
