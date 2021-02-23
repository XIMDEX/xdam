<?php

namespace Tests\Feature;

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
        $admin = $this->getUserWithRole(1);
        $manager = User::factory()->create(['name' => 'manager']);
        $new_manager = User::factory()->create(['name' => 'new_manager']);
        $editor = User::factory()->create(['name' => 'editor']);

        /*
            As $admin, create an organization and set it to $manager
        */
        $this->actingAs($admin, 'api');

        $org = $this->json('POST', '/api/v1/admin/organization/create', [
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
            'user_id' => $manager->id,
            'with_role_id' => "2",
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
            Switch authenticated user from $admin to $manager (with the $org set)
            Now, $manager will set role 'manager' to a $new_manager in $org
        */

        $this->actingAs($manager, 'api');
        $response = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $new_manager->id,
            'with_role_id' => '2',
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
            $manager set role 'manager' to a $new_manager in all workspaces of $org
        */
        $response = $this->json('POST', '/api/v1/organization/workspace/setAll/user', [
            'user_id' => $new_manager->id,
            'with_role_id' => '2',
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
            Now as $new_manager create a new workspace in $org.
            $new_manager is going to have "manager" role in the workspace created.
        */
        $this->actingAs($new_manager, 'api');
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
            As $new_manager, set an $org to $editor
        */
        $created = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $editor->id,
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

        /*
            Bouncer Bug: Bouncer permission is set in DB but when we get user->abilities at this point (in testing context)
            the user haven't abilities for the entity.
            This test can be tested step by step with postman, and will works.
            Pass test acting as $admin
        */

        $this->actingAs($admin, 'api');
        /*
        As $new_manager, set the $created_wsp_on_org to $editor
        */
        $created = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $editor->id,
            'with_role_id' => "3",
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
            As $new_manager, set role 'editor' to $editor on previous $created_wsp_on_org
        */
        $created = $this->json('POST', '/api/v1/role/user/set/abilitiesOnOrganizationOrWorkspace', [
            'user_id' => $editor->id,
            'role_id' => "3",
            'wo_id' => $wsp_entity->id,
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
