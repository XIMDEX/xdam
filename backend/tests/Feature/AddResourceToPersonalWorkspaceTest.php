<?php

namespace Tests\Feature;

use App\Enums\OrganizationType;
use App\Enums\WorkspaceType;
use App\Models\Collection;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AddResourceToPersonalWorkspaceTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_add_resource_personal()
    {
        /*
            Create users.
        */
        $user = User::factory()->create();

        /*
            as $user, we select our workspace to null (it's interpreted as personal workspace)
        */
        $this->actingAs($user, 'api');
        $wsp_user = $this->json('POST', '/api/v1/user/workspaces/select', [
            'workspace_id' => null,
        ]);

        $wsp_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);
        /*
            Now, upload the resource to the $user->selected_workspace (personal in this test).
        */

        Storage::fake('avatars');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $resource = $this->json('POST', '/api/v1/resource', [
            'File' => [$file],
            'type' => 'image',
            'name' => 'imagen test',
            'data' => '{"description": {"active": true, "partials": {"pages": 10}}}',
            'workspace_id' => $user->selected_workspace
        ]);

        $resource
            ->assertStatus(200)
            ->assertJson([
                'id' => true
            ]);

        /*
            Attach the resource to public organization.
            Now every user can "see" the resource in the public workspace
        */

        $response = $this->json('POST', '/api/v1/user/resource/collection/attach', [
            'collection_id' => Collection::where([
                'type_id' => 1,
                'organization_id' => Organization::where('type', OrganizationType::public)->first()->id
            ])->first()->id,
            'resource_id' => $resource->original->id,

        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => true
            ]);

    }
}
