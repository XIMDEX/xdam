<?php


namespace App\Traits;

use App\Enums\Abilities;
use App\Enums\OrganizationType;
use App\Enums\Roles;
use App\Models\Collection;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Workspace;
use Silber\Bouncer\BouncerFacade;
use Silber\Bouncer\Database\Role as DatabaseRole;

trait OnCreateOrganization
{
    protected static function bootOnCreateOrganization() {
        static::created(function ($model) {
            $solariumConnections = config('solarium.connections', []);

            //create collections for organization
            foreach ($solariumConnections as $keyName => $value) {
                Collection::create([
                    'name' => $model->name . ' ' . ucfirst($keyName) .' collection',
                    'organization_id' => $model->id,
                    'solr_connection' => $keyName
                ]);
            }
            // if($model->type != OrganizationType::public) {
                //Create default owner & corporate role
                $corp = DatabaseRole::create([
                    'name' => Roles::CORPORATE_WORKSPACE_MANAGEMENT,
                    'organization_id' => $model->id,
                    'applicable_to_entity' => Workspace::class,
                ]);

                BouncerFacade::allow($corp)->to(array_merge(
                    [Abilities::MANAGE_WORKSPACE],
                    Abilities::resourceManagerAbilities()), Workspace::class
                );

                $owner = DatabaseRole::create([
                    'name' => Roles::RESOURCE_OWNER,
                    'organization_id' => $model->id,
                    'applicable_to_entity' => Workspace::class,
                ]);

                BouncerFacade::allow($owner)->to(array_merge(
                    [Abilities::MANAGE_WORKSPACE],
                    Abilities::resourceManagerAbilities()), Workspace::class
                );

        });
    }
}
