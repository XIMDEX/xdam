<?php


namespace App\Traits;

use App\Models\Collection;
use App\Models\CollectionType;

trait OnCreateOrganization
{
    protected static function bootOnCreateOrganization() {
        static::created(function ($model) {

            //create collections for organization
            foreach (CollectionType::all() as $collection_type) {
                Collection::create([
                    'name' => $model->name . ' ' . $collection_type->name .' collection',
                    'organization_id' => $model->id,
                    'type_id' => $collection_type->id,
                    'solr_connection' => $collection_type->name
                ]);
            }
        });
    }
}
