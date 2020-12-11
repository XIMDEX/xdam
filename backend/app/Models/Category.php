<?php

namespace App\Models;

use App\Enums\ResourceType;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = ['name', 'type'];

    protected $casts = [
        'type' => ResourceType::class,
    ];

    public function hasDamResource(DamResource $resource)
    {
        return $this->resources()->where('dam_resource_id', $resource->id)->exists();
    }

    public function resources() {
        return $this->belongsToMany(DamResource::class);
    }
}
