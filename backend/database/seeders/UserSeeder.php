<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


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
            'name' => 'Admin user',
            'email' => 'admin@xdam.com',
            'password' => Hash::make('123123')
        ]);
        User::create([
            'name' => 'Gestor user',
            'email' => 'gestor@xdam.com',
            'password' => Hash::make('123123')
        ]);

        User::create([
            'name' => 'Editor user',
            'email' => 'editor@xdam.com',
            'password' => Hash::make('123123')
        ]);

        User::create([
            'name' => 'Lector user',
            'email' => 'lector@xdam.com',
            'password' => Hash::make('123123')
        ]);

    }
}
