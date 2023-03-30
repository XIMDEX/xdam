<?php

declare(strict_types=1);

namespace App\Services;

class BaseService
{

    public static function handleSchema($schema)
    {
        return $schema;
    }

    public static function handleFacetCard($facets)
    {
        return $facets;
    }

    public static function handleFacetValues($values, $facet)
    {
        return $values;
    }
}
