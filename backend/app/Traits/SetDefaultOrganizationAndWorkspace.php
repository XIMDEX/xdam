<?php


namespace App\Traits;

use App\Enums\Abilities;
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
            $adminService = new AdminService();
            $public_org = Organization::where('name', DefaultOrganizationWorkspace::public_organization)->first();
            $public_wsp = Workspace::where('name', DefaultOrganizationWorkspace::public_workspace)->first();
            $user_id = $model->id;


            $personal_wsp = Workspace::create([
                'name' => 'user #' . $model->id .' '. DefaultOrganizationWorkspace::personal_workspace,
                'type' => WorkspaceType::personal
            ]);

            $adminService->setOrganizations($user_id, $public_org->id, Roles::editor);
            $adminService->setWorkspaces($user_id, $public_wsp->id);
            $adminService->setWorkspaces($user_id, $personal_wsp->id);
            $model->allow(Abilities::canViewWorkspace, $public_wsp);
            $model->allow('*', $personal_wsp);
        });
    }
}
