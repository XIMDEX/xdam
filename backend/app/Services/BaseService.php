<?php

declare(strict_types=1);

namespace App\Services;
class BaseService
{    
    const TYPES_ALLOWS = ['string', 'integer', 'boolean', 'enum'];
    
    public static $array = [];
    public static $type_service = 'base';

    public function __construct() {}

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
        if (in_array($facet, array_keys(self::$array))) {
            $resourceName = self::$array[$facet];
            if (class_exists("App\\Models\\{$resourceName}")) {
                $model = app("App\\Models\\{$resourceName}");
                $model_instance = new $model();
            }
            $items = self::$type_service !== 'base' 
                ? $model::where('type', self::$type_service)->get()
                : $model::all();
            $values_names = array_keys(get_object_vars($values));
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
                if ($facet === 'categories' && $key ==='type') {
                    $field_add['value'] = self::$type_service;
                }
                $fields[] = $field_add;
            }
            foreach ($items as $item) {
                $name = strtolower($item->name);
                if (!in_array($name, $values_names)) {
                    $values->{$name} = [
                        'count' => 0,
                        'selected' => false,
                        'radio' => $values->{$values_names[0]}['radio']
                    ];
                }
                $values->{$name}['canEdit'] = true;
                $values->{$name}['canDelete'] = true;
                $values->{$name}['values'] = $item->toArray();
                $values->{$name}['fields'] = $fields;

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
                $values->{$name}['route'] = route($route, $opt);
                $values->{$name}['route_delete'] = route($route_delete, $opt);

            }
        }

        return $values;
    }
    
    protected static function parseTypeBBDD($type) {
        if ($type === 'enum') return 'string';
        return $type;
    }
}
