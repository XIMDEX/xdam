<?php

namespace Database\Seeders;

use App\Enums\CollectionType as EnumsCollectionType;
use App\Models\CollectionType;
use Illuminate\Database\Seeder;

class CollectionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $validatorsPath = config('solarium.solr_validators_folder');
        $schemasPath = config('solarium.solr_schemas_folder');

        foreach (EnumsCollectionType::valuesToArray() as $name) {
            CollectionType::create([
                'name' => $name,
                'solr_schema' => "$schemasPath/$name" . "_schema.json",
                'json_validator' => "$validatorsPath/$name" . '_validator.json',
            ]);
        }
    }
}
