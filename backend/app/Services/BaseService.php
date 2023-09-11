<?php

declare(strict_types=1);

namespace App\Services;
class BaseService
{

    public static function handleSchema($schema)
    {
        foreach ($schema->properties->description->properties as $key => $property) {
            if ($key === 'categories') {
                $categories = CategoryService::where('type', 'course');
                $schema->properties->description->properties->$key->options = $categories->all();
                $schema->properties->description->properties->$key->subType = 'dropdown';
            }
            if ($key === 'semantic_tags') {
                $schema->properties->description->properties->$key->subType = 'xtags';
            }

            if ($key === 'corporations') {
                $corporations = CorporationService::getAll();
                $schema->properties->description->properties->$key->options = $corporations;
                $schema->properties->description->properties->$key->subType = 'dropdown';
            }
        }
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
