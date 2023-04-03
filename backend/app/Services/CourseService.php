<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Corporation;

class CourseService extends BaseService
{
    const ADDABLE_ITEM_FACETS = ['categories' => 'Category', 'corporations' => 'Corporation'];
    const TYPES_ALLOWS = ['string', 'integer', 'boolean'];

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

    public static function handleFacetValues($values, $facet)
    {
        if (in_array($facet, array_keys(self::ADDABLE_ITEM_FACETS))) {
            $resourceName = self::ADDABLE_ITEM_FACETS[$facet];
            if (class_exists("App\\Models\\{$resourceName}")) {
                $model = app("App\\Models\\{$resourceName}");
                $model_instance = new $model();
            }
            $items = $model::where('type', 'course')->get();
            $values_names = array_keys(get_object_vars($values));
            $fillables = $model_instance->getFillable();
            $required = defined("{$resourceName}::REQUIRED_FILLABLES") ? $model::REQUIRED_FILLABLES : $fillables;

            foreach ($required as $key) {
                $type = $model_instance->getConnection()->getSchemaBuilder()->getColumnType($model_instance->getTable(), $key);
                $fields[] = [
                    'key' => $key,
                    'label' => str_replace('_', ' ', ucfirst($key)),
                    'type' => in_array($type, self::TYPES_ALLOWS) ? self::parseTypeBBDD($type) : false
                ];
            }
            foreach ($items as $item) {
                $name = strtolower($item->name);
                if (!in_array($name, $values_names)) {
                    $values->$name = [
                        'count' => 0,
                        'selected' => false,
                        'radio' => $values->{$values_names[0]}['radio']
                    ];
                }
                $values->$name['canEdit'] = true;
                $values->$name['canDelete'] = true;
                $values->$name['values'] = $item->toArray();

                $values->$name['fields'] = $fields;

                // $required = defined("{$resourceName}::REQUIRED_FILLABLES") ? $model::REQUIRED_FILLABLES : $fillables;

                if ($facet === 'categories') {
                    $route = 'v1category.update';
                    $route_delete = 'v1category.delete';
                    $opt = ['category' => $item->id];
                }
                if ($facet === 'corporations') {
                    $route = 'v1corporation.update';
                    $route_delete = 'v1corporation.delete';
                    $opt = ['corporation' => $item->id];
                }
                $values->$name['route'] = route($route, $opt);
                $values->$name['route_delete'] = route($route_delete, $opt);


                // $required = (new Category())->getFillable();

                // foreach ($required as $key) {
                //     $type = (new Category())->getConnection()->getSchemaBuilder()->getColumnType((new Category())->getTable(), $key);
                //     $values->$name['fields'][] = [
                //         'key' => $key,
                //         'label' => str_replace('_', ' ', ucfirst($key)),
                //         'type' => in_array($type, self::TYPES_ALLOWS) ? self::parseTypeBBDD($type) : false
                //     ];
                // }
            }
        }

        return $values;
    }

    private static function parseTypeBBDD($type) {
        return $type;
    }
}
