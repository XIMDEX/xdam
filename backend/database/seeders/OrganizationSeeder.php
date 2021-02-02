<?php

namespace Database\Seeders;

use App\Enums\DefaultOrganizationWorkspace;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id = Str::orderedUuid();
        DB::table('organizations')->insert([
            'id' => Str::orderedUuid(),
            'name' => DefaultOrganizationWorkspace::public_organization,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        //Organization::find($id);

    }
}
