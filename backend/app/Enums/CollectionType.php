<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class CollectionType extends Enum
{
    const multimedia = "multimedia";
    const course = "course";

    public static function valuesToArray()
    {
        $arr = [];

        foreach (self::getValues() as $v) {
            $arr[self::getDescription($v)] = $v;
        }
        return $arr;
    }
}
