<?php

namespace Tests\Feature;

use App\Enums\Roles;
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
    public $org;
    public $admin;
    public $super_admin;


    protected function setUp(): void
    {
        parent::setUp();
        //Create fake organization with workspaces

        $this->org = Organization::factory()
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->has(Workspace::factory(['name' => 'a generic faker wsp'])->count(1))
            ->create();

        /*
        Create users.
        we set admin role to $admin in $org
        then, create a $super_admin
        */
        $this->admin = $this->getUserWithRole(2, $this->org);
        $this->super_admin = $this->getUserWithRole(1);
    }

    public function test_set_admin_of_organization_on_setup()
    {
        if($this->admin->organizations()->where('id', $this->org->id)){
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    public function test_super_admin_set_to_admin_user_as_admin_of_a_generic_workspace_of_the_organization()
    {
        /*
            as $super_admin, the $admin must be related to any $org->workspaces
            In this case we attach it to $org->firstGenericWorkspace
        */
        $this->actingAs($this->super_admin, 'api');
        $wsp_user = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $this->admin->id,
            'workspace_id' => $this->org->firstGenericWorkspace()->id,
            'with_role_id' => Roles::admin_id
        ]);

        $wsp_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);
    }

    public function test_admin_selecets_in_which_workspace_he_is_going_to_upload()
    {

        /*
        now as $admin (with role admin in the organization and admin of the workspace)
        the $admin must select in which workspace is going to add the resource.
        It can be a public, personal or a specific wsp, like in this test, the $org->firstGenericWorkspace()
        */
        $this->setOrganization($this->admin, $this->org, Roles::admin_id);
        $this->actingAs($this->admin, 'api');

        $wsp_user = $this->json('POST', '/api/v1/user/workspaces/select', [
            'workspace_id' => $this->org->firstGenericWorkspace()->id,
        ]);

        $wsp_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);
    }

    public function test_upload_resource_to_selected_workspace_and_selected_collection()
    {
        /*
            The admin should select the collection in the front-end.
            The resource will be attached to collection of type 2 multimedia (02/2021).
            TODO: validate that the resource is a multimedia file so it can be attached to the Collection Type.
            Now, upload the resource to the $admin->selected_workspace.
        */
        $this->setOrganization($this->admin, $this->org, Roles::admin_id);
        $this->actingAs($this->admin, 'api');

        Storage::fake('avatars');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->json('POST', '/api/v1/resource', [
            'File' => [$file],
            'type' => 'image',
            'data' => '{"description": {"active": true, "partials": {"pages": 10}}}',
            'collection_id' => $this->org->collections()->where('type_id', 2)->first()->id,
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

        $response = $this->json('POST', '/api/v1/resource/'.$this-> org->collections()->where('type_id', 2)->first()->id.'/create', [
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
