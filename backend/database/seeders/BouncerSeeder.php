<?php

namespace Database\Seeders;

use App\Enums\Abilities;
use App\Enums\Roles;
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
        Bouncer::allow(Roles::super_admin)->everything();

        Bouncer::allow(Roles::admin)->to([
            Abilities::ManageRoles,
            Abilities::ManageOrganization,
            Abilities::ManageWorkspace,
        ]);

        Bouncer::allow(Roles::manager)->to([
            Abilities::ManageWorkspace,
        ]);

        Bouncer::allow(Roles::editor)->to([
            Abilities::ViewWorkspace,
            Abilities::UpdateWorkspace
        ]);

        Bouncer::allow(Roles::reader)->to([
            Abilities::ViewWorkspace,
        ]);
    }
}
