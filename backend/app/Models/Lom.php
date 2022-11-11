<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lom extends Model
{
    use HasFactory;

    protected $table = "resource_lom";
    protected $guarded = ['id'];

    private function getLOMAttributes()
    {
        $exceptions = ['id', 'dam_resource_id', 'created_at', 'updated_at'];
        $attributesValues = $this->attributesToArray();
        $resourceInfo = [];

        foreach ($attributesValues as $key => $value) {
            if (!in_array($key, $exceptions)) {
                $resourceInfo[$key] = $value;
            }
        }

        return $resourceInfo;
    }

    private function isValueValid($value)
    {
        if (gettype($value) === 'string') return strtolower($value) !== 'null';
        return $value !== null;
    }

    private function decodeResourceLOMValue(
        array &$values,
        string $key,
        $value,
        $subkey = null
    ) {
        if ($this->isValueValid($value)) {
            if (gettype($value) === 'string') {
                $auxValue = json_decode($value, true);
                $value = (!$this->isValueValid($auxValue) ? $value : $auxValue);
            }

            switch (gettype($value)) {
                case 'array':
                    foreach ($value as $subkey => $subvalue) {
                        $this->decodeResourceLOMValue($values, $key, $subvalue, $subkey);
                    }
                    break;

                default:
                    if ($this->isValueValid($value)) {
                        $entry = [
                            'key'       => $key,
                            'subkey'    => $subkey,
                            'value'     => $value
                        ];
                        $values[] = $entry;
                    }
                    break;
            }
        }
    }

    public function getResourceLOMValues(bool $allFields = true)
    {
        $facetFields = config('solr_facets', []);
        $attributes = $this->getLOMAttributes();
        $values = [];
        $defValues = [];

        foreach ($attributes as $k => $v) {
            $this->decodeResourceLOMValue($values, $k, $v);
        }

        if (!$allFields && array_key_exists('lom', $facetFields)) {
            foreach ($values as $item) {
                $selected = false;
    
                foreach ($facetFields['lom'] as $rItem) {
                    $check = ($rItem['key'] === $item['key']);
    
                    if ($rItem['subkey'] !== null) {
                        $check = $check && ($rItem['subkey'] === $item['subkey']);
                    }

                    if ($check) {
                        if ($rItem['key_alias'] !== null) $item['key'] = $rItem['key_alias'];
                        if ($rItem['subkey_alias'] !== null) $item['subkey'] = $rItem['subkey_alias'];
                        $selected = true;
                    }
                }
    
                if ($selected) $defValues[] = $item;
            }
        }

        return ($allFields ? $values : $defValues);
    }
}
