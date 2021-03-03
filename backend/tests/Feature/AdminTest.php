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
    // use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public $admin;
    public $anUser;
    public $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->anUser = User::factory()->create();
        $this->org = Organization::factory()
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->has(Workspace::factory(['name' => 'a generic faker wsp'])->count(1))
            ->create();
        $this->setOrganization($this->admin, $this->org, Roles::admin_id, false);
        $this->actingAs($this->admin, 'api');
    }

    public function test_setup_with_users_and_organization()
    {
        if($this->admin && $this->anUser && $this->org) {
            $all_created = true;
        }
        $this->assertTrue($all_created);
    }

    public function test_admin_attach_organization_to_an_user_with_role_editor()
    {
        /*
            Attach organization to $user
        */

        $response = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $this->anUser->id,
            'organization_id' => $this->org->id,
            'with_role_id' => Roles::editor_id
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
     * @depends test_admin_attach_organization_to_an_user_with_role_editor
     */

    public function test_attach_an_user_to_a_generic_workspace_of_organization()
    {

        $this->setOrganization($this->anUser, $this->org, Roles::editor_id);
        /*
            Attach a workspace to $user
        */
        $response = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $this->anUser->id,
            'workspace_id' => $this->org->corporateWorkspace()->id,
            'with_role_id' => Roles::admin_id
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

    public function test_set_role_to_an_user_on_specific_workspace()
    {
        $this->setOrganization($this->anUser, $this->org, Roles::editor_id);
        /*
        / SET USER ROLE ON SPECIFIC WORKSPACE. IT REQUIRES THE USER ATACHED TO THE ORGANIZATION OF WORKSPACE
        */

        $response = $this->json('POST', '/api/v1/role/user/set/abilitiesOnEntity', [
            'user_id' => $this->anUser->id,
            'role_id' => Roles::editor_id,
            'entity_id' => $this->org->workspaces()->where('name', 'a generic faker wsp')->first()->id,
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

    public function test_unset_role_to_an_user_on_specific_workspace()
    {
        $this->setOrganization($this->anUser, $this->org, Roles::editor_id, false);
        /*
            UNSET USER ROLE ON SPECIFIC WORKSPACE
        */

        $response = $this->json('POST', '/api/v1/role/user/set/abilitiesOnEntity', [
            'user_id' => $this->anUser->id,
            'role_id' => Roles::admin_id,
            'entity_id' => $this->org->workspaces()->where('name', 'a generic faker wsp')->first()->id,
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

    public function test_unattach_workspace_to_an_user()
    {
        $this->setOrganization($this->anUser, $this->org, Roles::editor_id, false);
        /*
            UNATTACH WORKSPACES TO USER
        */

        $response = $this->json('POST', '/api/v1/workspace/unset/user', [
            'user_id' => $this->anUser->id,
            'workspace_id' => $this->org->corporateWorkspace()->id,
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

    public function test_unattach_organization_to_an_user()
    {
        $this->setOrganization($this->anUser, $this->org, Roles::editor_id);
        /*
            UNATTACH ORGANIZATIONS TO USER
        */
        $response = $this->json('POST', '/api/v1/organization/unset/user', [
            'user_id' => $this->anUser->id,
            'organization_id' => $this->org->id,
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
