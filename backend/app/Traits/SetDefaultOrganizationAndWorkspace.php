<?php

namespace App\Traits;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\Workspace;
use App\Services\Admin\AdminService;

trait SetDefaultOrganizationAndWorkspace
{
    protected static function bootSetDefaultOrganizationAndWorkspace() {
        static::created(function ($model) {

            $adminService = new AdminService(new Roles);
            $public_org = Organization::where('name', DefaultOrganizationWorkspace::public_organization)->first();
            $public_wsp = Workspace::where('name', DefaultOrganizationWorkspace::public_workspace)->first();

            $adminService->setOrganizations($model->id, $public_org->id, $adminService->rolesService->ORGANIZATION_USER_ID());
            $adminService->setWorkspaces($model->id, $public_wsp->id, $adminService->rolesService->WORKSPACE_READER_ID());

            $model->selected_workspace = Workspace::where('type', WorkspaceType::public)->first()->id;
            $model->save();
        });
    }
}
