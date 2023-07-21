<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Corporation extends Model
{
    use HasFactory;

    protected $table = "corporation";

    protected $fillable = ["name", "description", "type", "is_default"];

    const  REQUIRED_FILLABLES = ["name", "description"];
}
