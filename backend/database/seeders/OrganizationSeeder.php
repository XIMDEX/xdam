<?php

namespace Database\Seeders;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\OrganizationType;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Organization::create([
            'name' => DefaultOrganizationWorkspace::public_organization,
            'type' => OrganizationType::public,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        Organization::create([
            'name' => "Escuela N-5",
            'type' => OrganizationType::corporate,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
