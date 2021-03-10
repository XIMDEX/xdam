<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Roles extends Enum
{
    const super_admin = 'super-admin';
    const admin = 'admin';
    const manager = 'manager';
    const editor = 'editor';
    const reader = 'reader';

    const super_admin_id = 1;
    const admin_id = 2;
    const manager_id = 3;
    const editor_id = 4;
    const reader_id = 5;
}
