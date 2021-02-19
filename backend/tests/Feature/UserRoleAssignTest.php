<?php

namespace Tests\Feature;

use App\Enums\OrganizationType;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserRoleAssignTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_role_assign()
    {
        $admin = $this->getUserWithRole(1);

        $org = Organization::factory()
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->has(Workspace::factory(['name' => 'a generic faker wsp'])->count(1))
            ->create();

        $gestor = $this->getUserWithRole(2);
        $new_gestor = User::factory()->create(['name' => 'new_gestor']);
        $editor = User::factory()->create(['name' => 'editor']);

        /*
            As $admin, set an $org to $gestor
        */
        $this->actingAs($admin, 'api');
        $response = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $gestor->id,
            'with_role_id' => "2",
            'organization_id' => $org->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'success' => true,
                ],
            ]);

        /*
            Switch authenticated user from $admin to $gestor (with the $org set)
            Now, $gestor will set role 'gestor' to a $new_gestor in $org
        */

        $this->actingAs($gestor, 'api');
        $response = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $new_gestor->id,
            'with_role_id' => '2',
            'organization_id' => $org->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'success' => true,
                ],
            ]);

        /*
            $gestor set role 'gestor' to a $new_gestor in all workspaces of $org
        */
        $response = $this->json('POST', '/api/v1/organization/workspace/setAll/user', [
            'user_id' => $new_gestor->id,
            'with_role_id' => '2',
            'organization_id' => $org->id,
        ]);


        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'user' => true,
                    'log' => true,
                ],
            ]);

        /*
            Now as $new_gestor create a new workspace in $org
        */
        $this->actingAs($new_gestor, 'api');
        $created_wsp_on_org = $this->json('POST', '/api/v1/organization/workspace/create', [
            'organization_id' => $org->id,
            'name' => 'El - Workspace'
        ]);

        $created_wsp_on_org
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['name' => true],
            ]);

        /*
            As $new_gestor, set an $org to $editor
        */
        $created = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => $editor->id,
            'with_role_id' => "3",
            'organization_id' => $org->id,
        ]);

        $created
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'success' => true,
                ],
            ]);


        /*
            TODO: FIX THIS
            As $new_gestor, set role 'editor' to $editor on previous $created_wsp_on_org
        */
        $created = $this->json('POST', '/api/v1/role/user/set/abilitiesOnOrganizationOrWorkspace', [
            'user_id' => $editor->id,
            'role_id' => "3",
            'wo_id' => $created_wsp_on_org->original->id,
            // 'wo_id' => $org->workspaces()->where('name', 'a generic faker wsp')->first()->id,
            'type' => 'set',
            'on' => 'wsp',
        ]);

        $created
            ->assertStatus(200)
            ->assertJson([
                'data'=> [
                    'user' => true,
                    'set_abilities' => true,
                    'on_wsp' => true,
                ],
            ]);

    }
}
