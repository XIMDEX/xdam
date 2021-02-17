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
        $user_role = $this->getUserWithRole(1);

        $this->actingAs($user_role, 'api');

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
        $response = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => (string)$user->id,
            'organization_id' => (string)$org->id,
            'with_role_id' => '4'
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
        $generic_wsp = (string)$org->workspaces()->where('name', 'a generic faker wsp')->first()->id;
        $generic_wsp2 = (string)$org2->workspaces()->where('name', 'a generic faker wsp 2')->first()->id;

        $response = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => (string)$user->id,
            'workspace_id' => (string)$org->corporateWorkspace()->id,
            'with_role_id' => 2
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
        / SET USER ROLE ON SPECIFIC WORKSPACE
        /
        */

        $response = $this->json('POST', '/api/v1/role/user/set/abilitiesOnOrganizationOrWorkspace', [
            'user_id' => (string)$user->id,
            'role_id' => "2",
            'wo_id' => $generic_wsp2,
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

        /*
        /
        / UNSET USER ROLE ON SPECIFIC WORKSPACE
        /
        */

        $response = $this->json('POST', '/api/v1/role/user/set/abilitiesOnOrganizationOrWorkspace', [
            'user_id' => (string)$user->id,
            'role_id' => "2",
            'wo_id' => $generic_wsp2,
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

        /*
        /
        / UNATTACH WORKSPACES TO USER
        /
        */

        $response = $this->json('POST', '/api/v1/workspace/unset/user', [
            'user_id' => (string)$user->id,
            'workspace_id' => (string)$org->corporateWorkspace()->id,
            'with_role_id' => 2
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
        $response = $this->json('POST', '/api/v1/organization/unset/user', [
            'user_id' => (string)$user->id,
            'organization_id' => (string)$org->id,

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
