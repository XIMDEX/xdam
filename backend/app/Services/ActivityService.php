<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ResourceType;

class ActivityService extends BaseService
{
    public function __construct() {
        parent::__construct();
        self::$type_service = ResourceType::activity;
        self::$array = ['workspaces' => 'Workspace'];
    }


    public static function handleFacetCard($facets)
    {
        $facets = parent::handleFacetCard($facets);
        $facets = self::addWorkspace( ResourceType::activity,$facets,array_keys(self::$array));

        return $facets;
    }

    public static function handleSchema($schema)
    {
        $schema = parent::handleSchema($schema);

        foreach ($schema->properties->description->properties as $key => $property) {
            if ($key === 'categories') {
                $categories = CategoryService::where('type', 'activity');
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

}
