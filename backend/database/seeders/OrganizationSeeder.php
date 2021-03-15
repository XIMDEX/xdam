<?php

namespace Database\Seeders;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\OrganizationType;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\Workspace;
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

        //FACTORY

        Organization::factory(['name' => 'MHE'])
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->create();

        Organization::factory(['name' => 'SEK'])
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->create();

        Organization::factory(['name' => 'Other Orgainzation'])
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->create();

    }
}
