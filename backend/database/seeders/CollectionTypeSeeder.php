<?php

namespace Database\Seeders;

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
        CollectionType::create([
            'name' => 'multimedia',
        ]);

        CollectionType::create([
            'name' => 'course',
        ]);
    }
}
