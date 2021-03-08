<?php

namespace Database\Seeders;

use App\Enums\Abilities;
use App\Enums\Roles;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Workspace;
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
        Bouncer::allow(Roles::SUPER_ADMIN)->everything();

        //CONTEXT: ORGANIZATION ROLES
        Bouncer::allow(Roles::ORGANIZATION_ADMIN)->to([Abilities::MANAGE_ROLES, Abilities::MANAGE_ORGANIZATION], Organization::class);
        $this->setEntityType(Roles::ORGANIZATION_ADMIN, Organization::class);

        Bouncer::allow(Roles::ORGANIZATION_MANAGER)->to([Abilities::MANAGE_ORGANIZATION_WORKSPACES, Abilities::CREATE_WORKSPACE], Organization::class);
        $this->setEntityType(Roles::ORGANIZATION_MANAGER, Organization::class);

        Bouncer::allow(Roles::ORGANIZATION_USER)->to(Abilities::BASIC_ORGANIZATION_USER, Organization::class);
        $this->setEntityType(Roles::ORGANIZATION_USER, Organization::class);

        //CONTEXT: WORKSPACE ROLES
        Bouncer::allow(Roles::WORKSPACE_MANAGER)->to(array_merge(
            [Abilities::MANAGE_WORKSPACE],
            Abilities::resourceManagerAbilities()), Workspace::class
        );
        $this->setEntityType(Roles::WORKSPACE_MANAGER, Workspace::class);

        Bouncer::allow(Roles::WORKSPACE_EDITOR)->to(array_merge(
            [Abilities::UPDATE_WORKSPACE, Abilities::READ_WORKSPACE],
            Abilities::resourceEditorAbilities()), Workspace::class
        );
        $this->setEntityType(Roles::WORKSPACE_EDITOR, Workspace::class);

        Bouncer::allow(Roles::WORKSPACE_READER)->to(array_merge(
            [Abilities::READ_WORKSPACE],
            Abilities::resourceReaderAbilities()), Workspace::class
        );
        $this->setEntityType(Roles::WORKSPACE_READER, Workspace::class);
    }

    public function setEntityType($rol, $entity_type)
    {
        $rol = Role::where('name', $rol)->first();
        $rol->applicable_to_entity = $entity_type;
        $rol->save();
    }


    // public function run()
    // {
    //     Bouncer::allow(Roles::super_admin)->everything();

    //     //Global and predefinded Organization and Workspace roles
    //     Bouncer::allow(Roles::admin)->to(array_merge(
    //         [Abilities::MANAGE_ROLES, Abilities::MANAGE_ORGANIZATION, Abilities::MANAGE_WORKSPACE],
    //         Abilities::resourceManagerAbilities()
    //     ));

    //     Bouncer::allow(Roles::manager)->to(array_merge(
    //         [
    //             Abilities::MANAGE_WORKSPACE,
    //         ],
    //         Abilities::resourceManagerAbilities())
    //     );

    //     Bouncer::allow(Roles::editor)->to(array_merge(
    //         [
    //             Abilities::READ_WORKSPACE,
    //             Abilities::UPDATE_WORKSPACE
    //         ],
    //         Abilities::resourceEditorAbilities())
    //     );

    //     Bouncer::allow(Roles::reader)->to(array_merge(
    //         [
    //             Abilities::READ_WORKSPACE
    //         ],
    //         Abilities::resourceReaderAbilities())
    //     );
    // }
}
