<?php

namespace Database\Seeders;

use App\Enums\Abilities;
use App\Enums\ResourceRoles;
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

        //Global and predefinded Organization and Workspace roles
        Bouncer::allow(Roles::admin)->to(array_merge(
            [
                Abilities::MANAGE_ROLES,
                Abilities::MANAGE_ORGANIZATION,
                Abilities::MANAGE_WORKSPACE
            ],
            Abilities::resourceManagerAbilities())
        );

        Bouncer::allow(Roles::manager)->to(array_merge(
            [
                Abilities::MANAGE_WORKSPACE
            ],
            Abilities::resourceManagerAbilities())
        );

        Bouncer::allow(Roles::editor)->to(array_merge(
            [
                Abilities::READ_WORKSPACE,
                Abilities::UPDATE_WORKSPACE
            ],
            Abilities::resourceEditorAbilities())
        );

        Bouncer::allow(Roles::reader)->to(array_merge(
            [
                Abilities::READ_WORKSPACE
            ],
            Abilities::resourceReaderAbilities())
        );
    }
}
