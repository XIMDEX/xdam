<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

class RolesInOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $org = Organization::find(4);

        Bouncer::role()->firstOrCreate([
            'name' => 'resource-reader',
            'organization_id' => $org->id
        ]);

        Bouncer::role()->firstOrCreate([
            'name' => 'resource-manager',
            'organization_id' => $org->id
        ]);
    }
}
