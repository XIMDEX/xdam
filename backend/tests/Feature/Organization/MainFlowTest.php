<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class MainFlowTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_super_admin_creates_organization()
    {
        $super_admin = $this->getUserWithRole(Roles::super_admin_id, null);
        $admin = User::factory()->create();

        $this->actingAs($super_admin, 'api');

        //set organization to user by admin
        $org = $this->json('POST', '/api/v1/super-admin/organization/create', [
            'name' => 'organization-testing' . Str::random(4),
        ]);

        $org
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'name' => true,
                    'created_at' => true,
                ],
            ]);

        return [
            'super_admin' => $super_admin,
            'manager' => $admin,
            'org' => $org->original
        ];


    }

    /**
    * @depends test_super_admin_creates_organization
    */
    public function test_super_admin_creates_a_generic_workspace_in_organization(array $data)
    {
        $this->actingAs($data['super_admin'], 'api');

        $wsp = $this->json('POST', '/api/v1/organization/workspace/create', [
            'name' => 'geneic-workspace-testing' . Str::random(4),
            'organization_id' => $data['org']->id
        ]);

        $wsp
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'name' => true,
                    'id' => true,
                ],
            ]);


        $data['wsp'] = $wsp->original;
        return $data;
    }

    /**
    * @depends test_super_admin_creates_a_generic_workspace_in_organization
    */
    public function test_super_admin_set_organization_to_other_user_as_manager_of_it(array $data)
    {
        $this->actingAs($data['super_admin'], 'api');

        $response = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $data['manager']->id,
            'with_role_id' => Roles::manager_id,
            'organization_id' => $data['org']->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'success' => true,
                ],
            ]);
    }


    /**
    * @depends test_super_admin_creates_a_generic_workspace_in_organization
    */
    public function test_super_admin_set_manager_role_to_other_user_in_a_generic_workspace_of_organization(array $data)
    {
        $this->actingAs($data['super_admin'], 'api');

        $response = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $data['manager']->id,
            'with_role_id' => Roles::manager_id,
            'workspace_id' => $data['wsp']->id,
        ]);


        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'user' => true,
                    'log' => ['success' => true],
                ],
            ]);
    }

    /**
    * @depends test_super_admin_creates_organization
    */
    public function test_super_admin_unset_organization_to_user(array $data)
    {
        $this->actingAs($data['super_admin'], 'api');
        //unset organization to user by admin. It should detach related workspaces of organization
        $response = $this->json('POST', '/api/v1/organization/unset/user', [
            'user_id' => $data['manager']->id,
            'with_role_id' => Roles::manager_id,
            'organization_id' => $data['org']->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'user' => true,
                    'log' => true,
                ],
            ]);
    }
}
