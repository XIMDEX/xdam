<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_setup_with_users_and_organization()
    {
        $admin = User::factory()->create();
        $anUser = User::factory()->create();
        $org = Organization::factory()
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->has(Workspace::factory(['name' => 'a generic faker wsp'])->count(1))
            ->create();

        $this->setOrganization($admin, $org, Roles::ORGANIZATION_ADMIN_ID);

        $this->actingAs($admin, 'api');

        $data = array('admin' => $admin, 'anUser' => $anUser, 'org' => $org);

        $this->assertTrue(isset($data));
        return $data;
    }

    /**
    * @depends test_setup_with_users_and_organization
    */
    public function test_admin_attach_organization_to_an_user_with_basic_user_role($data)
    {
        $this->actingAs($data['admin'], 'api');
        /*
            Attach organization to $user
        */

        $response = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $data['anUser']->id,
            'organization_id' => $data['org']->id,
            'with_role_id' => Roles::ORGANIZATION_USER_ID
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'success' => true
                ],
            ]);
    }

    /**
     * @depends test_setup_with_users_and_organization
     */

    public function test_attach_an_user_to_a_generic_workspace_of_organization($data)
    {
        $this->actingAs($data['admin'], 'api');
        /*
            Attach a workspace to $user
        */
        $response = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $data['anUser']->id,
            'workspace_id' => $data['org']->firstGenericWorkspace()->id,
            'with_role_id' => Roles::WORKSPACE_EDITOR_ID
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => true,
                    'log' => true
                ],
            ]);
    }


    /**
     * @depends test_setup_with_users_and_organization
     */
    public function test_admin_set_higher_role_to_an_user_on_generic_workspace($data)
    {
        $this->actingAs($data['admin'], 'api');

        $response = $this->json('POST', '/api/v1/role/user/set/abilitiesOnEntity', [
            'user_id' => $data['anUser']->id,
            'role_id' => Roles::WORKSPACE_MANAGER_ID,
            'entity_id' => $data['org']->workspaces()->where('name', 'a generic faker wsp')->first()->id,
            'type' => 'set',
            'on' => 'wsp',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => true,
                    'set_abilities' => true,
                    'on_wsp' => true,
                ],
            ]);
    }

    /**
     * @depends test_setup_with_users_and_organization
     */
    public function test_admin_unset_higher_role_to_an_user_on_specific_workspace($data)
    {
        $this->actingAs($data['admin'], 'api');
        /*
            UNSET USER ROLE ON SPECIFIC WORKSPACE
        */

        $response = $this->json('POST', '/api/v1/role/user/set/abilitiesOnEntity', [
            'user_id' => $data['anUser']->id,
            'role_id' => Roles::WORKSPACE_MANAGER_ID,
            'entity_id' => $data['org']->workspaces()->where('name', 'a generic faker wsp')->first()->id,
            'type' => 'unset',
            'on' => 'wsp',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => true,
                    'unset_abilities' => true,
                    'on_wsp' => true,
                ],
            ]);
    }

    /**
     * @depends test_setup_with_users_and_organization
     */
    public function test_admin_detach_from_one_generic_workspace_to_an_user($data)
    {
        $this->actingAs($data['admin'], 'api');
        /*
            UNATTACH WORKSPACES TO USER
        */

        $response = $this->json('POST', '/api/v1/workspace/unset/user', [
            'user_id' => $data['anUser']->id,
            'workspace_id' => $data['org']->firstGenericWorkspace()->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => true,
                    'log' => true
                ],
            ]);
    }

    /**
     * @depends test_setup_with_users_and_organization
     */
    public function test_admin_detach_organization_to_an_user($data)
    {

        $this->actingAs($data['admin'], 'api');

        $response = $this->json('POST', '/api/v1/organization/unset/user', [
            'user_id' => $data['anUser']->id,
            'organization_id' => $data['org']->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'log' => true,
                ],
            ]);
    }
}
