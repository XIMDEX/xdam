<?php

namespace Database\Seeders;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Workspace::create([
            'name' => DefaultOrganizationWorkspace::public_workspace,
            'organization_id' => Organization::where('name', DefaultOrganizationWorkspace::public_organization)->first()->id,
            'type' => WorkspaceType::public,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        Workspace::create([
            'name' => 'Escuela N-5 - Corporation',
            'organization_id' => 2,
            'type' => WorkspaceType::corporation,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        Workspace::create([
            'name' => 'Primero A',
            'organization_id' => 2,
            'type' => WorkspaceType::generic,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);


    }
}
