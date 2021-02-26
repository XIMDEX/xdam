<?php

namespace App\Models;

use App\Enums\WorkspaceType;
use App\Traits\OnCreateOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory, OnCreateOrganization;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'type'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function workspaces()
    {
        return $this->hasMany(Workspace::class);
    }

    public function corporateWorkspace()
    {
        return $this->workspaces()->where('type', WorkspaceType::corporate)->first();
    }

    public function firstGenericWorkspace()
    {
        return $this->workspaces()->where('type', WorkspaceType::generic)->first();
    }

    public function publicWorkspace()
    {
        return $this->workspaces()->where('type', WorkspaceType::public)->first();
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * Get all of the roles for the Organization
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }
}
