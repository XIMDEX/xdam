<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspaceUser extends Model
{
    use HasFactory;

    protected $table = "user_workspace";

    protected $fillable = ["user_id", "workspace_id"];
}
