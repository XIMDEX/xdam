<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class OrganizationType extends Enum
{
    const personal = "personal";
    const corporate = "corporate";
    const public = "public";
}
