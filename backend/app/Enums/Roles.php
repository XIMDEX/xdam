<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Roles extends Enum
{
    const admin = 1;
    const manager = 2;
    const editor = 3;
    const reader = 4;
}
