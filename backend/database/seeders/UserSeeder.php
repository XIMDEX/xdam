<?php

namespace Database\Seeders;

use App\Models\User;
use App\Traits\SetDefaultOrganizationAndWorkspace;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'user test',
            'email' => 'test@test.com',
            'password' => Hash::make('123123')
        ]);

    }
}
