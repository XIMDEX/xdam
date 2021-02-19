<?php


namespace App\Traits;

use App\Enums\Abilities;
use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\OrganizationType;
use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\Workspace;
use App\Services\Admin\AdminService;

trait SetDefaultOrganizationAndWorkspace
{
    protected static function bootSetDefaultOrganizationAndWorkspace() {
        static::created(function ($model) {
            $adminService = new AdminService();
            $public_org = Organization::where('name', DefaultOrganizationWorkspace::public_organization)->first();
            $public_wsp = Workspace::where('name', DefaultOrganizationWorkspace::public_workspace)->first();
            $user_id = $model->id;

            $personal_org = Organization::create([
                'name' => 'user #' . $model->id .' '. DefaultOrganizationWorkspace::personal_organization,
                'type' => OrganizationType::personal
            ]);

            $personal_wsp = Workspace::create([
                'name' => 'user #' . $model->id .' '. DefaultOrganizationWorkspace::personal_workspace,
                'type' => WorkspaceType::personal,
                'organization_id' => $personal_org->id
            ]);
            $personal_org->workspaces()->save($personal_wsp);

            $adminService->setOrganizations($user_id, $public_org->id, Roles::editor);
            $adminService->setWorkspaces($user_id, $public_wsp->id, Roles::lector);

            $adminService->setOrganizations($user_id, $personal_org->id, Roles::gestor);
            $adminService->setWorkspaces($user_id, $personal_wsp->id, Roles::gestor);

            $model->allow('*', $personal_wsp);
            $model->allow('*', $personal_org);
        });
    }
}
