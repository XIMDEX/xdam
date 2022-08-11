<?php

namespace App\Models;

use App\Enums\WorkspaceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

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
        return $this->hasMany(User::class);
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

    public function toArray()
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name
        ];
    }
}
