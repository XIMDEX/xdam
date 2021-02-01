<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'id' => Str::orderedUuid(),
            'name' => 'professor',
            'guard_name' => 'web',
        ]);
        DB::table('roles')->insert([
            'id' => Str::orderedUuid(),
            'name' => 'student',
            'guard_name' => 'web',
        ]);

        DB::table('permissions')->insert([
            'id' => Str::orderedUuid(),
            'name' => 'professor_permissions',
            'guard_name' => 'web',
        ]);

        DB::table('permissions')->insert([
            'id' => Str::orderedUuid(),
            'name' => 'student_permissions',
            'guard_name' => 'web',
        ]);
    }
}
