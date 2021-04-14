<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = ['type_id', 'name', 'organization_id', 'solr_connection', 'accept'];
    protected $table = "collections";

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function resources()
    {
        return $this->hasMany(DamResource::class);
    }
}
