<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Abilities extends Enum
{
    const canCreateWorkspace = "create-workspace";
    const canUpdateWorkspace = "update-workspace";
    const canViewWorkspace = "view-workspace";
    const canDeleteWorkspace = "delete-workspace";

    const canCreateOrganization = "create-organization";
    const canUpdateOrganization = "update-organization";
    const canViewOrganization = "view-organization";
    const canDeleteOrganization = "delete-organization";

    const canManageRoles = "manage-roles";
    const canManageOrganization = "manage-organizations";
    const canManageWorkspace = "manage-workspaces";
}
