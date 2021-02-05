<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

class BouncerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Bouncer::allow('superadmin')->everything();

        // Bouncer::allow('admin')->everything();
        // Bouncer::forbid('admin')->toManage(User::class);

        // Bouncer::allow('gestor')->to('create', Post::class);
        // Bouncer::allow('gestor')->toOwn(Post::class);

        // Bouncer::allow('editor')->to('create', Post::class);
        // Bouncer::allow('editor')->toOwn(Post::class);

        // Bouncer::allow('lector')->to('create', Post::class);
        // Bouncer::allow('lector')->toOwn(Post::class);
    }
}
