<?php

namespace App\Models;

use App\Enums\Abilities;
use App\Enums\OrganizationType;
use App\Enums\WorkspaceType;
use App\Models\Collection;
use App\Models\OrganizationCDN;
use App\Traits\SetDefaultOrganizationAndWorkspace;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class User extends Authenticatable
{
    use HasRolesAndAbilities, HasApiTokens, HasFactory, Notifiable, SetDefaultOrganizationAndWorkspace;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function organizations()
    {
        return $this->belongsToMany(Organization::class);
    }

    public function organizations_cdn_collections($type = null)
    {
        $cdns = [];
        $collections = [];
        $organizations = $this->organizations()->get();

        foreach ($organizations as $item) {
            $aux = OrganizationCDN::where('organization_id', $item->id)
                    ->get();

            foreach ($aux as $subitem) {
                $cdns[] = $subitem->cdn()->first();
            }
        }

        foreach ($cdns as $item) {
            $cdn_collections = $item->cdn_collections()->get();

            foreach ($cdn_collections as $subitem) {
                $collection_item = Collection::where('id', $subitem->collection_id)
                                        ->first();
                
                if ($collection_item !== null) {
                    if ($type === null) {
                        $collections[] = $collection_item;
                    } else if ($collection_item->solr_connection === $type) {
                        $collections[] = $collection_item;
                    }
                }
            }
        }

        echo '<pre>' . var_export($collections, true) . '</pre>';
    }

    public function canGetCatalogue(): bool
    {
        return true;
        // $org = $collection->organization()->first();
        // foreach ($this->organizations()->get() as $user_org) {
        //     if($org->id == $user_org->id) {
        //         return true;
        //     }
        // }
        // return false;
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class);
    }

    public function resources()
    {
        return DamResource::where('user_owner_id', $this->id)->get();
    }

    public function ownResource(DamResource $damResource): bool
    {
        return $this->id == $damResource->user_owner_id;
    }

    public function abilitiesOnEntity($entity_id, $entity_type)
    {
        $abilities_on_entity = [];

        foreach ($this->getAbilities() as $ability) {
            if($ability->entity_id == $entity_id && $ability->entity_type == $entity_type) {
                $abilities_on_entity[] = $ability;
            }
        }
        return $abilities_on_entity;
    }

    public function isAdminOf(Organization $organization)
    {
        return $this->can(Abilities::MANAGE_ORGANIZATION, $organization);
    }

}
