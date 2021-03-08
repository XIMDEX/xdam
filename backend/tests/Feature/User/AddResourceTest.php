<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\User;
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

    public function test_admin_selecets_in_which_workspace_he_is_going_to_upload()
    {
        //Create fake organization with workspaces

        $org = Organization::factory()
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->has(Workspace::factory(['name' => 'a generic faker wsp'])->count(1))
            ->create();

        $admin = $this->getUserWithRole(Roles::ORGANIZATION_ADMIN_ID, $org);

        /*
        As $admin (with role admin in the organization and workspaces)
        the $admin must select in which workspace is going to add the resource.
        It can be a public, personal or a specific wsp, like in this test, the $org->firstGenericWorkspace()
        */

        $this->actingAs($admin, 'api');

        $wsp_user = $this->json('POST', '/api/v1/user/workspaces/select', [
            'workspace_id' => $org->firstGenericWorkspace()->id,
        ]);

        $wsp_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);

        return [
            'org' => $org,
            'admin' => $admin
        ];
    }

    /**
    * @depends test_admin_selecets_in_which_workspace_he_is_going_to_upload
    */
    public function test_upload_resource_to_selected_workspace_and_selected_collection(array $data)
    {
        /*
            The admin should select the collection in the front-end.
            The resource will be attached to collection of type 2 multimedia (02/2021).
            TODO: validate that the resource is a multimedia file so it can be attached to the Collection Type.
            Now, upload the resource to the $admin->selected_workspace.
        */

        $this->actingAs($data['admin'], 'api');

        Storage::fake('avatars');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $collection_id = $data['org']->collections()->where('solr_connection', 'multimedia')->first()->id;
        $response = $this->json('POST', '/api/v1/resource', [
            'File' => [$file],
            'type' => 'image',
            'data' => '{"description": {"active": true, "partials": {"pages": 10}}}',
            'collection_id' => $collection_id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => true
            ]);

        /*
            Test the other addResource endpoint
        */

        Storage::fake('avatars');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->json('POST', '/api/v1/resource/'.$collection_id.'/create', [
            'File' => [$file],
            'type' => 'image',
            'data' => '{"description": {"active": true, "partials": {"pages": 10}}}',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => true
            ]);
    }
}
