<?php

namespace Tests\Feature;

use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AddResourceTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_add_resource()
    {
        //Create fake organization with workspaces
        $org = Organization::factory()
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->has(Workspace::factory(['name' => 'a generic faker wsp'])->count(1))
            ->create();

        /*
            Create users.
            we set gestor role to $user in $org
            then, create an $admin
        */
        $user = $this->getUserWithRole(2, $org);
        $admin = $this->getUserWithRole(1);

        /*
            as $admin, the $user must be related to any $org->workspaces
            In this case we attach it to $org->firstGenericWorkspace
        */
        $this->actingAs($admin, 'api');
        $wsp_user = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $user->id,
            'workspace_id' => $org->firstGenericWorkspace()->id,
            'with_role_id' => 2
        ]);

        $wsp_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);

        /*
            now as $user (with role gestor in the organization and set workspace)
            the user must select in which workspace is going to add the resource.
            It can be a public, personal or a specific wsp, like in this test, the $org->firstGenericWorkspace()
        */
        $this->actingAs($user, 'api');
        $wsp_user = $this->json('POST', '/api/v1/user/workspaces/select', [
            'workspace_id' => $org->firstGenericWorkspace()->id,
        ]);

        $wsp_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);
        /*
            The user should select the collection in the front-end.
            The resource will be attached to collection of type 2 multimedia (02/2021).
            TODO: validate that the resource is a multimedia file so it can be attached to the Collection Type.
            Now, upload the resource to the $user->selected_workspace.
        */

        Storage::fake('avatars');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->json('POST', '/api/v1/resource', [
            'File' => [$file],
            'type' => 'image',
            'data' => '{"description": {"active": true, "partials": {"pages": 10}}}',
            'collection_id' => $org->collections()->where('type_id', 2)->first()->id,
            'workspace_id' => $user->selected_workspace
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => true
            ]);
    }
}
