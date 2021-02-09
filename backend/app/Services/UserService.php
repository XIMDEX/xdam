<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class UserService
{

    public function user()
    {
        return Auth::user();
    }

    public function getWorkspaces()
    {
        return Auth::user()->workspaces()->get();
    }

    public function getOrganizations()
    {
        return Auth::user()->organizations()->get();
    }

    public function getWorkspacesOfOrganization($organization_id)
    {
        return Auth::user()->workspaces()->where('organization_id', $organization_id)->get();

    }
}
