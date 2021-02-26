<?php


namespace App\Traits;

use App\Enums\Abilities;
use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\OrganizationType;
use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Collection;
use App\Models\Organization;
use App\Models\Workspace;
use App\Services\Admin\AdminService;

trait SetDefaultOrganizationAndWorkspace
{
    protected static function bootSetDefaultOrganizationAndWorkspace() {
        static::created(function ($model) {
            if($model->id == 1) {
                return true;
            }

            $adminService = new AdminService();
            $public_org = Organization::where('name', DefaultOrganizationWorkspace::public_organization)->first();
            $public_wsp = Workspace::where('name', DefaultOrganizationWorkspace::public_workspace)->first();
            $adminService->setOrganizations($model->id, $public_org->id, Roles::reader_id);
            $adminService->setWorkspaces($model->id, $public_wsp->id, Roles::reader_id);

            // $personal_org = Organization::create([
            //     'name' => 'user #' . $model->id .' '. DefaultOrganizationWorkspace::personal_organization,
            //     'type' => OrganizationType::personal
            // ]);

            // $personal_wsp = Workspace::create([
            //     'name' => 'user #' . $model->id .' '. DefaultOrganizationWorkspace::personal_workspace,
            //     'type' => WorkspaceType::personal,
            //     'organization_id' => $personal_org->id
            // ]);
            //$personal_org->workspaces()->save($personal_wsp);

            // $adminService->setOrganizations($model->id, $personal_org->id, Roles::manager_id);
            // $adminService->setWorkspaces($model->id, $personal_wsp->id, Roles::manager_id);

            // $model->selected_workspace = $public_wsp->id;
            $model->save();
        });
    }
}
