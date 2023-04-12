<?php

namespace App\Models;

use App\Enums\ResourceType;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class Category extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = ['name', 'type'];

    protected static function booted()
    {
        static::saving(function ($category) {
            $category->validateType();
        });
    }

    protected function validateType()
    {
        $validator = Validator::make(
            ['type' => $this->type],
            ['type' => Rule::in(ResourceType::getValues())]
        );

        $validator->validate();
    }

    public function hasDamResource(DamResource $resource)
    {
        return $this->resources()->where('dam_resource_id', $resource->id)->exists();
    }

    public function resources()
    {
        return $this->belongsToMany(DamResource::class);
    }
}
