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
        foreach (EnumsCollectionType::valuesToArray() as $name) {
            CollectionType::create([
                'name' => $name,
            ]);
        }
    }
}
