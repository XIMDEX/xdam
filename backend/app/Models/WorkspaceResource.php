<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspaceResource extends Model
{
    use HasFactory;

    protected $table = "dam_resource_workspace";

    protected $fillable = ['workspace_id'. 'dam_resource_id'];
}
