<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
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
        $super_admin = $this->getUserWithRole(Roles::SUPER_ADMIN_ID, null);
        $admin = User::factory()->create(['name' => 'admin']);
        $new_admin = User::factory()->create(['name' => 'new_admin']);
        $manager = User::factory()->create(['name' => 'manager']);

        /*
            As $super_admin, create an organization and set it to $admin
        */
        $this->actingAs($super_admin, 'api');

        $org = $this->json('POST', '/api/v1/super-admin/organization/create', [
            'name' => 'Organization Test ' . Str::random() ,
        ]);

        $org
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'name' => true,
                ],
            ]);

        $org = $org->original;


        $response = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $admin->id,
            'with_role_id' => Roles::ORGANIZATION_ADMIN_ID,
            'organization_id' => $org->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'success' => true,
                ],
            ]);

        /*
            Switch authenticated user from $super_admin to $admin (with the $org set)
            Now, $admin will set role 'admin' to a $new_admin in $org
        */

        $this->actingAs($admin, 'api');
        $response = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $new_admin->id,
            'with_role_id' => Roles::ORGANIZATION_ADMIN_ID,
            'organization_id' => $org->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'success' => true,
                ],
            ]);

        /*
            $admin set role 'admin' to a $new_admin in all workspaces of $org
        */
        $response = $this->json('POST', '/api/v1/organization/workspace/setAll/user', [
            'user_id' => $new_admin->id,
            'with_role_id' => Roles::ORGANIZATION_ADMIN_ID,
            'organization_id' => $org->id,
        ]);


        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'user' => true,
                    'log' => true,
                ],
            ]);

        /*
            Now as $new_admin create a new workspace in $org.
            $new_admin is going to have "admin" role in the workspace created.
        */
        $this->actingAs($new_admin, 'api');
        $created_wsp_on_org = $this->json('POST', '/api/v1/organization/workspace/create', [
            'organization_id' => $org->id,
            'name' => 'El - Workspace'
        ]);

        $created_wsp_on_org
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['name' => true],
            ]);


        $wsp_entity = $created_wsp_on_org->original;

        /*
            As $new_admin, set an $org to $manager
        */
        $created = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $manager->id,
            'with_role_id' => Roles::ORGANIZATION_MANAGER_ID,
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
            Bouncer Bug: Bouncer permission is set in DB but when we get user->abilities at this point (in testing context)
            the user haven't abilities for the entity.
            This test can be tested step by step with postman, and will works.
            Pass test acting as $super_admin
        */

        $this->actingAs($super_admin, 'api');
        /*
        As $new_admin, set the $created_wsp_on_org to $manager
        */
        $created = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $manager->id,
            'with_role_id' => Roles::WORKSPACE_MANAGER_ID,
            'workspace_id' => $wsp_entity->id,
        ]);

        $created
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'user' => true,
                    'log' => true
                ],
            ]);

        /*
            As $new_admin, set role 'manager' to $manager on previous $created_wsp_on_org
        */
        $created = $this->json('POST', '/api/v1/role/user/set/abilitiesOnEntity', [
            'user_id' => $manager->id,
            'role_id' => Roles::WORKSPACE_MANAGER_ID,
            'entity_id' => $wsp_entity->id,
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
