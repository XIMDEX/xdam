<?php

namespace App\Models;

use App\Enums\MediaType;
use App\Enums\Roles;
use App\Enums\ThumbnailTypes;
use App\Models\Media as MediaModel;
use App\Traits\UsesUuid;
use App\Models\Workspace;
use App\Models\WorkspaceResource;
use App\Utils\Utils;
use Cartalyst\Tags\TaggableInterface;
use Cartalyst\Tags\TaggableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Silber\Bouncer\Database\Role;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DamResource extends Model implements HasMedia, TaggableInterface
{
    use HasFactory, TaggableTrait, InteractsWithMedia;

    protected $fillable = ['id', 'type', 'data', 'name', 'active', 'user_owner_id', 'collection_id'];

    protected $table = "dam_resources";

    public $incrementing = false;

    protected $casts = [
        "data" => "object",
        "id" => "string"
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

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasCategory(Category $category)
    {
        return $this->categories()->where('category_id', $category->id)->exists();
    }

    public function uses(): HasMany
    {
        return $this->hasMany(DamResourceUse::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class);
    }

    public function lomes(): HasOne
    {
        return $this->hasOne(Lomes::class);
    }

    public function lom(): HasOne
    {
        return $this->hasOne(Lom::class);
    }

    public function associatedMedia(): BelongsToMany
    {
        return $this->belongsToMany(MediaModel::class);
    }

    // public function organizations()
    // {
    //     $orgs = [];
    //     foreach ($this->workspaces()->get() as $wsp) {
    //         $orgs[] = $wsp->organization()->first()->id;
    //     }
    //     return $orgs;
    // }

    public function organization()
    {
        return $this->collection()->first()->organization()->first();
    }

    public function getUserAbilities(User $user): array
    {
        //get all workspaces where the resource is attached
        $workspaces_where_resource_is = $this->workspaces()->get();

        //now get all abilities that the user has in each of these workspaces
        $user_abilities_in_resource_workspaces = [];
        foreach ($workspaces_where_resource_is as $wsp) {

            foreach ($user->abilitiesOnEntity($wsp->id, Workspace::class) as $abilities) {
                $user_abilities_in_resource_workspaces[] = $abilities->toArray();
            }

            //set organization owner role if user is the owner
            if($this->user_owner_id == $user->id) {
                $org = $wsp->organization()->first();
                $resource_owner_abilities = Role::where(['organization_id' => $org->id, 'name' => Roles::RESOURCE_OWNER])->first()->abilities;
                foreach ($resource_owner_abilities as $abilities) {
                    $user_abilities_in_resource_workspaces[] = $abilities->toArray();
                }
            }
        }
        $abilities_final = [];

        foreach (Utils::unique_multidimensional_array($user_abilities_in_resource_workspaces, 'name') as $ability) {
            $abilities_final[] = $ability['name'];
        }

        return $abilities_final;
    }

    /**
     * Get user permissions on resource, then authorize if any of these abilities match with $ability_to_check
     * this means that the user has the $ability_to_check ability in some workspace where the resource is attached.
     * For READ_RESOURCE, also works if resource is attached in the public workspace
     */
    public function userIsAuthorized(User $user, string $ability_to_check): bool
    {
        foreach ($this->getUserAbilities($user) as $user_ability) {
            if($user_ability == $ability_to_check) {
                return true;
            }
        }
        return false;
    }

    public function updateWorkspace(Workspace $oldWorkspace, Workspace $newWorkspace)
    {
        $result = WorkspaceResource::where('dam_resource_id', $this->id)
                    ->where('workspace_id', $oldWorkspace->id)
                    ->update(['workspace_id' => $newWorkspace->id]);
        return true;
    }
}
