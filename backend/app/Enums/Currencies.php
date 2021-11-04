<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Currencies extends Enum
{
    //Workspace
    const EUR = "Euro";

    public static function getAllCurrencies()
    {
        return [
            self::EUR,
        ];
    }

}
