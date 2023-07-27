<?php

namespace App\Services;

use App\Utils\Utils;
use stdClass;

class LOMService {
    public function handleFacets(&$facetsArray, $facet, $facet_idx)
    {
        $schema = str_starts_with('lomes', $facet)  ? Utils::getLomesSchema(false) : Utils::getLomSchema(false);
        $schemaConfig = config('solr_facets.client.'.env('APP_CLIENT', 'DEFAULT'));
        if (count($schemaConfig) > 0) {

            $solrFacetsConfig = config('solr_facets.constants');
            $specialCharacter = $solrFacetsConfig['special_character'];
            $valueSeparator = Utils::getRepetitiveString($specialCharacter, $solrFacetsConfig['value_separator']);
            $charactersMap = $solrFacetsConfig['characters_map'];

            usort($charactersMap, function ($item1, $item2) {
                return $item2['to'] > $item1['to'];
            });

            $newFacets =  [];
            foreach ($facetsArray[$facet_idx]->values as $key => $data) {
                foreach ($schemaConfig as $facetConfig => $facet_data) {
                    if (str_starts_with($key, $facetConfig)) {
                        $index_tab = array_search($facet_data['key'], array_column($schema->tabs, 'key'));
                        foreach ($schema->tabs[$index_tab]->properties as $label => $facet_properties) {
                            if (str_starts_with($key, $facet_properties->data_field)) {
                                if (!isset($newFacets[$label])) {
                                    $newFacets[$label] = new StdClass();
                                    $newFacets[$label]->key = $facet;
                                    $newFacets[$label]->label = $facet_data['label'];
                                    $newFacets[$label]->values = new stdClass();
                                }
                                $newFacets[$label]->values->{$key} = $data;
                                $label_value = str_replace($facet_properties->data_field . $valueSeparator, '', $key);

                                foreach ($charactersMap as $chItem) {
                                    $auxCharacter = Utils::getRepetitiveString($specialCharacter, $chItem['to']);
                                    $label_value = str_replace($auxCharacter, $chItem['from'], $label_value);
                                }
                                $newFacets[$label]->values->{$key}['label'] = $label_value;
                            }
                        }
                    }
                }
            }

            foreach (array_values($newFacets) as $newFacet) {
                $facetsArray[] = $newFacet;
            }
            unset($facetsArray[$facet_idx]);
            $facetsArray = array_values($facetsArray);
        }
    }

    public static function handleSchema($json)
    {
        return $json;
    }
}
