<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Roles extends Enum
{
    //const super_admin = 'super-admin';
    // const admin = 'admin';
    // const manager = 'manager';
    // const editor = 'editor';
    // const reader = 'reader';

    // const super_admin_id = 1;
    // const admin_id = 2;
    // const manager_id = 3;
    // const editor_id = 4;
    // const reader_id = 5;

    const SUPER_ADMIN = 'super-admin';

    const ORGANIZATION_ADMIN = 'organization-admin';
    const ORGANIZATION_MANAGER = 'organization-manager';
    const ORGANIZATION_USER = 'organization-user';

    const WORKSPACE_MANAGER = 'workspace-manager';
    const WORKSPACE_EDITOR = 'workspace-editor';
    const WORKSPACE_READER = 'workspace-reader';

    const SUPER_ADMIN_ID = 1;

    const ORGANIZATION_ADMIN_ID = 2;
    const ORGANIZATION_MANAGER_ID = 3;
    const ORGANIZATION_USER_ID = 4;

    const WORKSPACE_MANAGER_ID = 5;
    const WORKSPACE_EDITOR_ID = 6;
    const WORKSPACE_READER_ID = 7;
}
