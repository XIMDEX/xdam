<?php

namespace App\Services;

use App\Models\Collection as ModelsCollection;
use App\Models\DamResource;

class CollectionService
{
    public function getLastResource(ModelsCollection $collection, $time): DamResource
    {
        $last = $collection->resources()->orderBy($time.'_at', 'desc')->first();
        return $last;
    }

    public function get(int $collectionId): ModelsCollection
    {
        return ModelsCollection::find($collectionId);
    }

    public function createOrganizationCollections(string $organizationId)
    {
        $solariumConnections = config('solarium.connections', []);

        //create collections for organization
        foreach ($solariumConnections as $keyName => $value) {
            ModelsCollection::create([
                'name' => ucfirst($keyName),
                'organization_id' => $organizationId,
                'solr_connection' => $keyName,
                'accept' => $value['accepts_types'][0]
            ]);
        }

        // //Create default owner & corporate role
        // $corp = BouncerRoles::create([
        //     'name' => Roles::CORPORATE_WORKSPACE_MANAGEMENT,
        //     'organization_id' => $model->id,
        //     'applicable_to_entity' => Workspace::class,
        // ]);

        // Bouncer::allow($corp)->to(
        //     array_merge(
        //         [Abilities::MANAGE_WORKSPACE],
        //         Abilities::resourceManagerAbilities()
        //     ),
        //     Workspace::class
        // );

        // $owner = BouncerRoles::create([
        //     'name' => Roles::RESOURCE_OWNER,
        //     'organization_id' => $model->id,
        //     'applicable_to_entity' => Workspace::class,
        // ]);

        // Bouncer::allow($owner)->to(
        //     array_merge(
        //         [Abilities::MANAGE_WORKSPACE],
        //         Abilities::resourceManagerAbilities()
        //     ),
        //     Workspace::class
        // );
    }
}
