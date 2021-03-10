<?php

namespace App\Models;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Enums\ThumbnailTypes;
use App\Traits\UsesUuid;
use Cartalyst\Tags\TaggableInterface;
use Cartalyst\Tags\TaggableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Solarium\QueryType\ManagedResources\RequestBuilder\Resource;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DamResource extends Model implements HasMedia, TaggableInterface
{
    use HasFactory, UsesUuid, TaggableTrait, InteractsWithMedia;

    protected $fillable = ['type', 'data', 'name', 'active', 'user_owner_id', 'collection_id'];

    protected $table = "dam_resources";

    protected $casts = [
        "data" => "object"
    ];

    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {

        $this->addMediaConversion(ThumbnailTypes::thumb_64x64)
            ->width(64)
            ->height(64)
            ->performOnCollections(MediaType::Preview()->key);

        $this->addMediaConversion(ThumbnailTypes::thumb_200x400)
            ->width(200)
            ->height(400)
            ->performOnCollections(MediaType::Preview()->key);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }


    public function hasCategory(Category $category)
    {
        return $this->categories()->where('category_id', $category->id)->exists();
    }

    public function uses()
    {
        return $this->hasMany(DamResourceUse::class);
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class);
    }
}
