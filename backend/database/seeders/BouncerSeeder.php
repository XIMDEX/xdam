<?php

namespace Database\Seeders;

use App\Enums\Abilities;
use App\Models\User;
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
        Bouncer::allow('gestor')->to(Abilities::canManageRoles);
        Bouncer::allow('gestor')->to(Abilities::canManageOrganization);
        Bouncer::allow('gestor')->to(Abilities::canManageWorkspace);

        Bouncer::allow('editor')->to(Abilities::canViewWorkspace);
        Bouncer::allow('editor')->to(Abilities::canUpdateWorkspace);

        Bouncer::allow('lector')->to(Abilities::canViewWorkspace);

        //User assign
        Bouncer::assign('admin')->to(User::find(1));
    }
}
