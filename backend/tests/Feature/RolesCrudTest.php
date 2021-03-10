<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class RolesCrudTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_create_role_in_organization()
    {
        /*
            Setup organization user and login
        */
        $org = Organization::factory()
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->has(Workspace::factory(['type' => WorkspaceType::generic])->count(1))
            ->create();

        $admin_of_org = User::factory()->create();
        $this->setOrganization($admin_of_org, $org, Roles::admin_id, false);
        $this->actingAs($admin_of_org, 'api');

        /*
            Create Role
        */

        $role = $this->json('POST', '/api/v1/organization/'.$org->id.'/roles/store', [
            'name' => 'role-technical-name',
            'title' => 'Role semantic name'
        ]);

        $role
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['name' => true],
            ]);

        return
            [
                'role' => $role->original,
                'org' => $org,
                'user' => $admin_of_org,
            ];

    }

    /**
    * @depends test_create_role_in_organization
    */
    public function test_list_roles_of_organization(array $data)
    {
        $this->actingAs($data['user'], 'api');
        /*
            List roles
        */
        $roles = $this->json('GET', '/api/v1/organization/'.$data['org']->id.'/roles/all');

        $roles
            ->assertStatus(200)
            ->assertJson([
                'data'=> [],
            ]);

        return $data;
    }

    /**
    * @depends test_create_role_in_organization
    */
    public function test_list_available_abilities_to_set_on_role(array $data)
    {
        $this->actingAs($data['user'], 'api');

        /*
            Set abilities role
        */

        $abilities = $this->json('GET', '/api/v1/organization/'.$data['org']->id.'/abilities/all');
        $abilities
            ->assertStatus(200)
            ->assertJson([
                'data'=> [],
            ]);

        $data['abilities'] = $abilities->original;
        return $data;
    }


    /**
    * @depends test_list_available_abilities_to_set_on_role
    */
    public function test_set_abilities_to_role_of_organization(array $data)
    {
        $this->actingAs($data['user'], 'api');

        /*
            Set abilities role
        */
        $show_resources_ability = $data['abilities'][4]->id;

        $role_with_ability = $this->json('POST', '/api/v1/organization/'.$data['org']->id.'/roles/set/ability', [
            'role_id' => $data['role']->id,
            'ability_ids' => [
                $show_resources_ability,
            ]
        ]);

        $role_with_ability
            ->assertStatus(200)
            ->assertJson([
                'data'=> [],
            ]);

        return $data;
    }

    /**
    * @depends test_create_role_in_organization
    */
    public function test_update_role_of_organization(array $data)
    {
        $this->actingAs($data['user'], 'api');

        /*
            Update role
        */

        $roles = $this->json('POST', '/api/v1/organization/'.$data['org']->id.'/roles/update',[
            'role_id' => $data['role']->id,
            'name' => 'new-name-for-role'
        ]);

        $roles
            ->assertStatus(200)
            ->assertJson([
                'data'=> [],
            ]);

        return $data;
    }

    /**
    * @depends test_create_role_in_organization
    */
    public function test_delete_roles_of_organization(array $data)
    {
        $this->actingAs($data['user'], 'api');

        /*
            Delete role
        */

        $roles = $this->json('DELETE', '/api/v1/organization/'.$data['org']->id.'/roles/'.$data['role']->id);
        $roles
            ->assertStatus(200)
            ->assertJson([
                'data'=> [],
            ]);

        return $data;
    }


}
