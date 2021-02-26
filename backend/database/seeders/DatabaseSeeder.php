<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CollectionTypeSeeder::class,
            OrganizationSeeder::class,
            WorkspaceSeeder::class,
            BouncerSeeder::class,
            UserSeeder::class,
            RolesInOrganizationSeeder::class
        ]);
    }
}
