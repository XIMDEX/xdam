<?php

namespace App\Models;

use App\Enums\ResourceType;
use App\Traits\UsesUuid;
use Cartalyst\Tags\TaggableInterface;
use Cartalyst\Tags\TaggableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DamResource extends Model implements HasMedia, TaggableInterface
{
    use HasFactory, UsesUuid, TaggableTrait, InteractsWithMedia;

    protected $fillable = ['type', 'data', 'name'];

    protected $table = "dam_resources";

    protected $casts = [
        'type' => ResourceType::class,
        "data" => "object"
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function hasCategory(Category $category)
    {
        return $this->categories()->where('category_id', $category->id)->exists();
    }

    public function uses()
    {
        return $this->hasMany(DamResourceUse::class);
    }
}
