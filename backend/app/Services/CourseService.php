<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Corporation;
use App\Enums\ResourceType;

class CourseService extends BaseService
{
    public function __construct() {
        parent::__construct();
        self::$array = ['categories' => 'Category', 'corporations' => 'Corporation'];
        self::$type_service = ResourceType::course;
    }

    public static function handleSchema($schema)
    {
        $schema = parent::handleSchema($schema);

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
        foreach ($facets as $index => $facet) {
            if (in_array($facet['key'], array_keys(self::ADDABLE_ITEM_FACETS))) {
                $facets[$index]['canAdd'] = true;
                $resourceName = self::ADDABLE_ITEM_FACETS[$facet['key']];
                if (class_exists("App\\Models\\{$resourceName}")) {
                    $model = app("App\\Models\\{$resourceName}");
                    $model_instance = new $model();
                }
                $fillables = $model_instance->getFillable();
                $required = defined("{$resourceName}::REQUIRED_FILLABLES") ? $model::REQUIRED_FILLABLES : $fillables;

                foreach ($required as $key) {
                    $type = $model_instance->getConnection()->getSchemaBuilder()->getColumnType($model_instance->getTable(), $key);
                    $facets[$index]['fields'][] = [
                        'key' => $key,
                        'label' => str_replace('_', ' ', ucfirst($key)),
                        'type' => in_array($type, self::TYPES_ALLOWS) ? self::parseTypeBBDD($type) : false
                    ];
                }
                if ($facet['key'] === 'categories') {
                    $route = 'v1category.store';
                }
                if ($facet['key'] === 'corporations') {
                    $route = 'v1corporation.store';
                }
                $facets[$index]['route'] = route($route);
            }
        }

        return $facets;
    }


}
