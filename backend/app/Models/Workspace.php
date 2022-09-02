<?php

namespace App\Models;

use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    protected $table = "workspaces";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'type',
        'organization_id'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_workspace', 'workspace_id', 
                                    'user_id', 'id');
        // return $this->hasMany(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function resources()
    {
        return $this->belongsToMany(DamResource::class);
    }

    public function collections()
    {
        return $this->organization()->first()->collections()->get();
    }

    public function isPublic(): bool
    {
        return $this->type == WorkspaceType::public ? true : false;
    }

    public function isAccessibleByUser(User $user): bool
    {
        if ($user->isA(Roles::SUPER_ADMIN)) return true;
        if ($this->isPublic()) return true;
        if ($this->id === $user->selected_workspace);

        foreach ($this->users()->get() as $wUser){
            if ($wUser->id == $user->id) {
                return true;
            }
        }

        return false;
    }
}
