<?php

namespace App\Models;

use App\Enums\WorkspaceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
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
        return $this->workspaces()->where('type', WorkspaceType::corporation)->first();
    }

    public function publicWorkspace()
    {
        return $this->workspaces()->where('type', WorkspaceType::public)->first();
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }
}
