<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Silber\Bouncer\BouncerFacade as Bouncer;

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
            'name' => 'Manager user',
            'email' => 'manager@xdam.com',
            'password' => Hash::make('123123')
        ]);

        User::create([
            'name' => 'Editor user',
            'email' => 'editor@xdam.com',
            'password' => Hash::make('123123')
        ]);

        User::create([
            'name' => 'Reader user',
            'email' => 'reader@xdam.com',
            'password' => Hash::make('123123')
        ]);


        Bouncer::assign('admin')->to(User::find(1));

    }
}
