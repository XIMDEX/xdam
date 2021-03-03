<?php


namespace App\Traits;

use App\Models\Collection;
use App\Models\CollectionType;

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
        });
    }
}
