<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Abilities extends Enum
{
    const canUpdateWorkspace = "update-workspace";
    const canViewWorkspace = "view-workspace";

    const canManageRoles = "manage-roles";
    const canManageOrganization = "manage-organizations";
    const canManageWorkspace = "manage-workspaces";
}
