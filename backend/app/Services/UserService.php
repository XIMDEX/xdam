<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;


class UserService
{

    public $user;

    public function __construct()
    {
        $this->user = User::find(Auth::user()->id);
    }

    public function user_auth()
    {
        return Auth::user();
    }

    public function user_model()
    {
        return $this->user;
    }

    public function setOrganization($orgID)
    {
        $this->user->organizations()->attach($orgID);
        return $this->user;
    }

    public function setWorkspace($wspID)
    {
        $this->user->workspaces()->attach($wspID);
        return $this->user;
    }

    public function getWorkspaces()
    {
        return $this->user->workspaces()->get();
    }

    public function getOrganizations()
    {
        return $this->user->organization()->get();
    }
}
