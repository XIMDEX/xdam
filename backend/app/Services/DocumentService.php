<?php

declare(strict_types=1);


namespace App\Services;

use App\Enums\ResourceType;
class DocumentService extends BaseService
{

    public function __construct() {
        parent::__construct();
        self::$array = ['categories' => 'Category', 'workspaces' => 'Workspace'];
        self::$type_service = ResourceType::document;
    }

    public static function handleSchema($schema)
    {
        $schema = parent::handleSchema($schema);

        foreach ($schema->properties->description->properties as $key => $property) {
            if ($key === 'categories') {
                $categories = CategoryService::where('type', 'document');
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
        $facets = parent::handleFacetCard($facets);
        foreach ($facets as $index => $facet) {
            if (in_array($facet['key'], array_keys(self::$array))) {
                $facets[$index]['canAdd'] = true;
                $resourceName = self::$array[$facet['key']];
                if (class_exists("App\\Models\\{$resourceName}")) {
                    $model = app("App\\Models\\{$resourceName}");
                    $model_instance = new $model();
                }
                $fillables = $model_instance->getFillable();
                $model_class = $model::class;
                $required = defined("{$model_class}::REQUIRED_FILLABLES") ? $model::REQUIRED_FILLABLES : $fillables;

                foreach ($required as $key) {
                    $type = $model_instance->getConnection()->getSchemaBuilder()->getColumnType($model_instance->getTable(), $key);
                    $field_add = [
                        'key' => $key,
                        'label' => str_replace('_', ' ', ucfirst($key)),
                        'type' => in_array($type, self::TYPES_ALLOWS) ? self::parseTypeBBDD($type) : false
                    ];
                    if ($facet['key'] === 'categories' && $key==='type') {
                        $field_add['value'] = ResourceType::document();
                    }
                    $facets[$index]['fields'][] = $field_add;
                }
                if ($facet['key'] === 'categories') {
                    $route = 'v1category.store';
                    $facets[$index]['route'] = route($route);
                }
                if ($facet['key'] === 'workspaces') {
                    $route = 'v1wsp.create';
                }

                if ($route) $facets[$index]['route'] = route($route);
            }
        }

            return $facets;
    }

}
