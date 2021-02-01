<?php

namespace Database\Seeders;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('workspaces')->insert([
            'id' => Str::orderedUuid(),
            'name' => DefaultOrganizationWorkspace::public_workspace,
            'organization_id' => Organization::where('name',DefaultOrganizationWorkspace::public_organization)->first()->id,
            'type' => WorkspaceType::public
        ]);
    }
}
