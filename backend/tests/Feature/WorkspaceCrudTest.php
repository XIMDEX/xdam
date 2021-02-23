<?php

namespace Tests\Feature;

use App\Enums\OrganizationType;
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
    public function test_workspace_crud()
    {

        $org = Organization::factory()
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->has(Workspace::factory(['type' => WorkspaceType::generic])->count(1))
            ->create();

        $admin = $this->getUserWithRole(1);
        $manager = $this->getUserWithRole(2, $org);
        $editor = $this->getUserWithRole(3);

        $this->actingAs($admin, 'api');

        $org_user = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $manager->id,
            'organization_id' => $org->id,
            'with_role_id' => 2
        ]);


        $org_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);


        //EDITOR
        $org_user = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $editor->id,
            'organization_id' => $org->id,
            'with_role_id' => 3
        ]);


        $org_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['success' => true],
            ]);


        $created_wsp = $this->json('POST', '/api/v1/organization/workspace/create', [
            'organization_id' => $org->id,
            'name' => $org->name . ' - Workspace'
        ]);


        $created_wsp
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['name' => true],
            ]);


        $this->actingAs($admin, 'api');

        $org_user = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $manager->id,
            'workspace_id' => $created_wsp->original->id,
            'with_role_id' => 2
        ]);


        $org_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);



        $manager_setted_to_wsp = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $editor->id,
            'workspace_id' => $created_wsp->original->id,
            'with_role_id' => 3
        ]);

        $manager_setted_to_wsp
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'user' => true,
                    'log' => true
                ],
            ]);


        $updated = $this->json('POST', '/api/v1/workspace/update', [
            'workspace_id' => $created_wsp->original->id,
            'name' => $org->name . ' - Workspace - updated'
        ]);

        $updated
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'updated' => [
                        'id' => true,
                        'name' => $org->name . ' - Workspace - updated'
                        ]
                    ],
            ]);

        $deleted = $this->delete('/api/v1/workspace/' . (string)$created_wsp->original->id);

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
