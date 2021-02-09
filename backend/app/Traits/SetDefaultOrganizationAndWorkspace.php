<?php


namespace App\Traits;

use App\Enums\Abilities;
use App\Enums\DefaultOrganizationWorkspace;
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
            $adminService->setOrganizations($user_id, [$public_org->id]);
            $adminService->setWorkspaces($user_id, [$public_wsp->id]);
            $model->allow(Abilities::canViewWorkspace, $public_wsp);
        });
    }
}
