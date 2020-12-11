<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamResourceUse extends Model
{
    use HasFactory;

    protected $fillable = ['used_in', 'related_to', 'dam_resource_id'];

    public function resource()
    {
        return $this->belongsTo(DamResource::class);
    }
}
