<?php

namespace Database\Seeders;

use App\Enums\Abilities;
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

        Bouncer::allow('admin')->everything();

        //Workspaces
        Bouncer::allow('manager')->to(Abilities::canManageRoles);
        Bouncer::allow('manager')->to(Abilities::canManageOrganization);
        Bouncer::allow('manager')->to(Abilities::canManageWorkspace);

        Bouncer::allow('editor')->to(Abilities::canViewWorkspace);
        Bouncer::allow('editor')->to(Abilities::canUpdateWorkspace);

        Bouncer::allow('reader')->to(Abilities::canViewWorkspace);

    }
}
