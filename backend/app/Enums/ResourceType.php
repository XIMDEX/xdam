<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class ResourceType extends Enum
{
    const document = 0;
    const video = 1;
    const image = 2;
    const audio = 3;
    const url = 4;
    const course = 5;
}
