<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class AccessPermission extends Enum
{
    const default = "default";
    const ipAddress = "ip_address";
    const lti = "lti";
    const originUrl = "origin_url";

    public static function existsAccessPermissionType($typeToCheck)
    {
        return $typeToCheck == self::default || $typeToCheck == self::originUrl;
    }
}
