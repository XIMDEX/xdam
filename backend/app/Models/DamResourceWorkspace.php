<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamResourceWorkspace extends Model
{
    use HasFactory;

    protected $table = "dam_resource_workspace";

    protected $fillable = ['workspace_id', 'dam_resource_id'];

    public function workspace()
    {
        return $this->hasOne(Workspace::class);
    }

    public function resource()
    {
        return $this->hasOne(DamResource::class);
    }
}
