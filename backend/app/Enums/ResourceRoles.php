<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class ResourceRoles extends Enum
{
    const r_manager = 'r_manager';
    const r_editor = 'r_editor';
    const r_reader = 'r_reader';
}
