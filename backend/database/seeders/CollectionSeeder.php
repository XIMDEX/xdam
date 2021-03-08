<?php

namespace Database\Seeders;

use App\Models\Collection;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Collection::create([
           'name' =>  "Book collection",
           'organization_id' => 1,
           'solr_connection' => "book"
       ]);
    }
}
