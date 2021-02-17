<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Workspace;
use Error;
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
            ->has(Workspace::factory(['type' => 'corporation'])->count(1))
            ->has(Workspace::factory(['type' => 'generic'])->count(1))
            ->create();

        $user = $this->getUserWithRole(2, $org);
        $this->actingAs($user, 'api');

        $org_user = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'with_role_id' => 2
        ]);

        $org_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['success' => true],
            ]);


        $created_wsp = $this->json('POST', '/api/v1/workspace/create', [
            'organization_id' => $org->id,
            'name' => $org->name . ' - Workspace'
        ]);
        $created_wsp
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['name' => true],
            ]);



        $user_setted_to_wsp = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $user->id,
            'workspace_id' => $created_wsp->original->id,
            'with_role_id' => 2
        ]);


        $user_setted_to_wsp
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
