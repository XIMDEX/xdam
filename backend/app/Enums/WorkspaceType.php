<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class WorkspaceType extends Enum
{
    const generic = "generic";
    const personal = "personal";
    const corporation = "corporation";
    const public = "public";
}
