<?php

namespace App\Utils;

use App\Models\Workspace;

class Utils
{

    public static function unique_multidimensional_array($array, $key): array
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public static function workspacesToName(array $ids): array
    {
        $array_of_names = [];

        foreach (Workspace::find($ids) as $wsp) {
            $array_of_names[] = $wsp->name;
        }

        return $array_of_names;
    }

    public static function arrayToObject($array)
    {
        // First we convert the array to a json string
        $json = json_encode($array);

        // The we convert the json string to a stdClass()
        $object = json_decode($json);

        return $object;
    }
}
