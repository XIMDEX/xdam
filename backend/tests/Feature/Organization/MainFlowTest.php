<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MainFlowTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_main_flow()
    {
        $admin = $this->getUserWithRole(Roles::super_admin_id);
        $userRand = User::factory()->create();
        $this->actingAs($admin, 'api');
        //set organization to user by admin
        $org = Organization::find(2);

        $response = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $userRand->id,
            'with_role_id' => Roles::manager_id,
            'organization_id' => $org->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'success' => true,
                ],
            ]);


        //set workspace of organizatio to user by admin
        $response = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $userRand->id,
            'with_role_id' => Roles::manager_id,
            'workspace_id' => $org->workspaces()->where('type', WorkspaceType::generic)->first()->id,
        ]);


        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'user' => true,
                    'log' => ['success' => true],
                ],
            ]);

        //unset organization to user by admin. It should detach related workspaces of organization
        $response = $this->json('POST', '/api/v1/organization/unset/user', [
            'user_id' => $userRand->id,
            'with_role_id' => Roles::manager_id,
            'organization_id' => '2',
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
