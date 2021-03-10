<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class WorkspaceType extends Enum
{
    const generic = "generic";
    const personal = "personal";
    const corporate = "corporate";
    const public = "public";
}
