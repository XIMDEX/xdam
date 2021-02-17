<?php

namespace Tests\Feature;

use App\Enums\Abilities;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Silber\Bouncer\BouncerFacade;
use Silber\Bouncer\Database\Role;
use Tests\TestCase;

class UserRoleAssignTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_role_assign()
    {
        $user = User::factory()->create();
        $gestor_rol = Role::find(2);
        $abilities = [];
        foreach ($gestor_rol->getAbilities()->toArray() as $ability) {
            $abilities[] = $ability['name'];
        }
        $user_to_assign_role = User::factory()->create();
        $user_with_no_permission = User::factory()->create();

        $org = Organization::factory()
            ->has(Workspace::factory(['type' => 'corporation'])->count(1))
            ->has(Workspace::factory(['name' => 'a generic faker wsp'])->count(1))
            ->create();

        BouncerFacade::allow($user)->to($abilities, $org);

        $this->actingAs($user, 'api');

        $created = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $user_to_assign_role->id,
            'with_role_id' => "2",
            'organization_id' => $org->id,
        ]);

        $created
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'success' => true,
                ],
            ]);


        /*
        /
        / USER CHANGE
        /
        */
        $this->actingAs($user_to_assign_role, 'api');

        $created_wsp_on_org = $this->json('POST', '/api/v1/workspace/create', [
            'organization_id' => $org->id,
            'name' => $org->name . ' - Workspace'
        ]);

        $created_wsp_on_org
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['name' => true],
            ]);

        //CHECK
        BouncerFacade::allow($user_to_assign_role)->to(Abilities::canManageWorkspace, $created_wsp_on_org->original);
        BouncerFacade::allow($user_to_assign_role)->to(Abilities::canManageRoles, $created_wsp_on_org->original);


        $created = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $user_with_no_permission->id,
            'with_role_id' => "3",
            'organization_id' => $org->id,
        ]);

        $created
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'success' => true,
                ],
            ]);

        //$user_with_no_permission needs to be seted to the organization first
        $created = $this->json('POST', '/api/v1/role/user/set/abilitiesOnOrganizationOrWorkspace', [
            'user_id' => $user_with_no_permission->id,
            'role_id' => "3",
            'wo_id' => $created_wsp_on_org->original->id,
            'type' => 'set',
            'on' => 'wsp',
        ]);

        $created
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'user' => true,
                    'set_abilities' => true,
                    'on_wsp' => true,
                ],
            ]);

    }
}
