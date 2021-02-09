<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class DefaultOrganizationWorkspace extends Enum
{
    const public_organization = "Public Organization";
    const public_workspace = "Public Workspace";
    const personal_organization = "Personal Organization";
    const personal_workspace = "Personal Workspace";
}
