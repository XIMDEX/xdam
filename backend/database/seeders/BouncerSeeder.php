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
        $this->setEntityTypeAndSystemDefault(Roles::SUPER_ADMIN, null);

        //CONTEXT: ORGANIZATION ROLES
        Bouncer::allow(Roles::ORGANIZATION_ADMIN)->to([Abilities::MANAGE_ROLES, Abilities::MANAGE_ORGANIZATION], Organization::class);
        $this->setEntityTypeAndSystemDefault(Roles::ORGANIZATION_ADMIN, Organization::class);

        Bouncer::allow(Roles::ORGANIZATION_MANAGER)->to([Abilities::MANAGE_ORGANIZATION_WORKSPACES, Abilities::CREATE_WORKSPACE], Organization::class);
        $this->setEntityTypeAndSystemDefault(Roles::ORGANIZATION_MANAGER, Organization::class);

        Bouncer::allow(Roles::ORGANIZATION_USER)->to(Abilities::BASIC_ORGANIZATION_USER, Organization::class);
        $this->setEntityTypeAndSystemDefault(Roles::ORGANIZATION_USER, Organization::class);

        //CONTEXT: WORKSPACE ROLES
        Bouncer::allow(Roles::WORKSPACE_MANAGER)->to(array_merge(
            [Abilities::MANAGE_WORKSPACE],
            Abilities::resourceManagerAbilities()), Workspace::class
        );
        $this->setEntityTypeAndSystemDefault(Roles::WORKSPACE_MANAGER, Workspace::class);

        Bouncer::allow(Roles::WORKSPACE_EDITOR)->to(array_merge(
            [Abilities::UPDATE_WORKSPACE, Abilities::READ_WORKSPACE],
            Abilities::resourceEditorAbilities()), Workspace::class
        );
        $this->setEntityTypeAndSystemDefault(Roles::WORKSPACE_EDITOR, Workspace::class);

        Bouncer::allow(Roles::WORKSPACE_READER)->to(array_merge(
            [Abilities::READ_WORKSPACE],
            Abilities::resourceReaderAbilities()), Workspace::class
        );
        $this->setEntityTypeAndSystemDefault(Roles::WORKSPACE_READER, Workspace::class);
    }

    public function setEntityTypeAndSystemDefault($rol, $entity_type)
    {
        $rol = Role::where('name', $rol)->first();
        $rol->applicable_to_entity = $entity_type;
        $rol->system_default = 1;
        $rol->save();
    }

}
