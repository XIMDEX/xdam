<?php

namespace Tests\Feature;

use App\Enums\OrganizationType;
use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class WorkspaceCrudTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_setup_users_and_organizations()
    {

        $org = Organization::factory()
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->has(Workspace::factory(['type' => WorkspaceType::generic])->count(1))
            ->create();

        $admin = $this->getUserWithRole(Roles::SUPER_ADMIN_ID, null);
        $manager = $this->getUserWithRole(2, $org);
        $editor = $this->getUserWithRole(3, $org);

        $data = array('admin' => $admin, 'org' => $org, 'manager' => $manager, 'editor' => $editor);
        $this->assertTrue(isset($data));
        return $data;
    }


    /**
    * @depends test_setup_users_and_organizations
    */
    public function test_admin_set_organization_to_manager($data)
    {
        $this->actingAs($data['admin'], 'api');
        /*
         $admin Set Organization to $manager
        */
        $org_user = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $data['manager']->id,
            'organization_id' => $data['org']->id,
            'with_role_id' => Roles::ORGANIZATION_ADMIN_ID
        ]);

        $org_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);

        return $data;
    }

    /**
    * @depends test_admin_set_organization_to_manager
    */
    public function test_admin_set_organization_to_editor($data)
    {
        $this->actingAs($data['admin'], 'api');
        /*
         $admin Set Organization to $editor
        */
        $org_user = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $data['editor']->id,
            'organization_id' => $data['org']->id,
            'with_role_id' => Roles::ORGANIZATION_MANAGER_ID
        ]);


        $org_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true,
            ]);

        return $data;
    }

    /**
    * @depends test_admin_set_organization_to_editor
    */
    public function test_admin_create_a_new_workspace_in_the_organization($data)
    {
        $this->actingAs($data['admin'], 'api');
        /*
         $admin Create a newe Workspace in the Organization
        */
        $created_wsp = $this->json('POST', '/api/v1/organization/workspace/create', [
            'organization_id' => $data['org']->id,
            'name' => $data['org']->name . ' - Workspace'
        ]);


        $created_wsp
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['name' => true],
            ]);

        $data['wsp'] = $created_wsp->original;
        return $data;
    }

    /**
    * @depends test_admin_create_a_new_workspace_in_the_organization
    */
    public function test_admin_set_the_created_workspace_to_manager($data)
    {
        $this->actingAs($data['admin'], 'api');
        /*
         $admin Set created workspace to $manager
        */

        $org_user = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $data['manager']->id,
            'workspace_id' => $data['wsp']->id,
            'with_role_id' => Roles::WORKSPACE_MANAGER_ID
        ]);


        $org_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);

        return $data;
    }

    /**
    * @depends test_admin_set_the_created_workspace_to_manager
    */
    public function test_admin_set_the_created_workspace_to_editor($data)
    {
        $this->actingAs($data['admin'], 'api');

        /*
         $admin Set created workspace to $editor
        */

        $manager_setted_to_wsp = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $data['editor']->id,
            'workspace_id' => $data['wsp']->id,
            'with_role_id' => Roles::WORKSPACE_MANAGER_ID
        ]);

        $manager_setted_to_wsp
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'user' => true,
                    'log' => true
                ],
            ]);

        return $data;

    }

    /**
    * @depends test_admin_set_the_created_workspace_to_editor
    */
    public function test_manager_update_the_created_worksapce($data)
    {
        $this->actingAs($data['manager'], 'api');
        /*
            $manager Update created workspace
        */
        $updated = $this->json('POST', '/api/v1/workspace/update', [
            'workspace_id' => $data['wsp']->id,
            'name' => $data['org']->name . ' - Workspace - updated'
        ]);

        $updated
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'updated' => [
                        'id' => true,
                        'name' => $data['org']->name . ' - Workspace - updated'
                        ]
                    ],
            ]);

        return $data;
    }

    /**
    * @depends test_admin_set_the_created_workspace_to_editor
    */
    public function test_editor_update_the_created_worksapce($data)
    {
        $this->actingAs($data['editor'], 'api');
        /*
            $manager Update created workspace
        */
        $updated = $this->json('POST', '/api/v1/workspace/update', [
            'workspace_id' => $data['wsp']->id,
            'name' => $data['org']->name . ' - Workspace - updated'
        ]);

        $updated
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'updated' => [
                        'id' => true,
                        'name' => $data['org']->name . ' - Workspace - updated'
                        ]
                    ],
            ]);

        return $data;
    }

    /**
    * @depends test_manager_update_the_created_worksapce
    */
    public function test_manager_delete_the_workspace($data)
    {
        $this->actingAs($data['manager'], 'api');
        /*
            $manager Delete created workspace
        */
        $deleted = $this->delete('/api/v1/workspace/' . (string)$data['wsp']->id);

        $deleted
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'deleted' => [
                        'id' => true
                        ]
                    ],
            ]);
    }

}
