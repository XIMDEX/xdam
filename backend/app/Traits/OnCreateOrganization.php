<?php


namespace App\Traits;

use App\Enums\Abilities;
use App\Enums\Roles;
use App\Models\Collection;
use App\Models\Workspace;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Silber\Bouncer\Database\Role as BouncerRoles;

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
                    'solr_connection' => $keyName,
                    'accept' => $value['accepts_types'][0]
                ]);
            }

            //Create default owner & corporate role
            $corp = BouncerRoles::create([
                'name' => Roles::CORPORATE_WORKSPACE_MANAGEMENT,
                'organization_id' => $model->id,
                'applicable_to_entity' => Workspace::class,
            ]);

            Bouncer::allow($corp)->to(array_merge(
                [Abilities::MANAGE_WORKSPACE],
                Abilities::resourceManagerAbilities()), Workspace::class
            );

            $owner = BouncerRoles::create([
                'name' => Roles::RESOURCE_OWNER,
                'organization_id' => $model->id,
                'applicable_to_entity' => Workspace::class,
            ]);

            Bouncer::allow($owner)->to(array_merge(
                [Abilities::MANAGE_WORKSPACE],
                Abilities::resourceManagerAbilities()), Workspace::class
            );

        });
    }
}
