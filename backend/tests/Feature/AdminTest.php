<?php

namespace Tests\Feature;

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
    public function test_admin_features()
    {
        $this->actingAs($this->getUser(['admin', 'gestor'], ['*']), 'api');

        $user = User::factory()->create();
        $org = Organization::factory()
            ->has(Workspace::factory(['type' => 'corporation'])->count(1))
            ->has(Workspace::factory(['name' => 'a generic faker wsp'])->count(1))
            ->create();

        $org2 = Organization::factory()
            ->has(Workspace::factory(['type' => 'corporation'])->count(1))
            ->has(Workspace::factory(['name' => 'a generic faker wsp 2'])->count(1))
            ->create();


        /*
        /
        / ATTACH ORGANIZATIONS TO USER
        /
        */
        $response = $this->json('POST', '/api/v1/admin/user/setOrganizations', [
            'user_id' => (string)$user->id,
            'organization_ids' => [(string)$org->id, (string)$org2->id],
        ]);


        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'success' => true
                ],
            ]);

        /*
        /
        / ATTACH WORKSPACES TO USER
        /
        */
        $response = $this->json('POST', '/api/v1/admin/user/setWorkspaces', [
            'user_id' => (string)$user->id,
            'workspace_ids' => [
                (string)$org->corporateWorkspace()->id,
                (string)$org2->corporateWorkspace()->id,
                (string)Workspace::where('type', WorkspaceType::public)->first()->id,
                (string)$org->workspaces()->where('name', 'a generic faker wsp')->first()->id,
                (string)$org2->workspaces()->where('name', 'a generic faker wsp 2')->first()->id
            ],

        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => true,
                    'log' => true
                ],
            ]);

        /*
        /
        / UNATTACH WORKSPACES TO USER
        /
        */

        $response = $this->json('POST', '/api/v1/admin/user/unsetWorkspaces', [
            'user_id' => (string)$user->id,
            'workspace_ids' => [
                (string)$org->corporateWorkspace()->id,
                (string)$org2->corporateWorkspace()->id,
                (string)Workspace::where('type', WorkspaceType::public)->first()->id,
                (string)$org->workspaces()->where('name', 'a generic faker wsp')->first()->id,
                (string)$org2->workspaces()->where('name', 'a generic faker wsp 2')->first()->id
            ],

        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => true,
                    'log' => true
                ],
            ]);
        /*
        /
        / UNATTACH ORGANIZATIONS TO USER
        /
        */
        $response = $this->json('POST', '/api/v1/admin/user/unsetOrganizations', [
            'user_id' => (string)$user->id,
            'organization_ids' => [
                (string)$org->id,
                (string)$org2->id
            ],

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
