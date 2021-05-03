<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lomes extends Model
{
    use HasFactory;

    protected $table = "resource_lomes";
    protected $guarded = ['id'];
}
