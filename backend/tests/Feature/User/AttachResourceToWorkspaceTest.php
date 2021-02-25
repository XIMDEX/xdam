<?php

namespace Tests\Feature;

use App\Enums\Abilities;
use App\Enums\Entities;
use App\Enums\MediaType;
use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Http\Resources\MediaResource;
use App\Http\Resources\ResourceResource;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use App\Utils\DamUrlUtil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Silber\Bouncer\BouncerFacade;
use Silber\Bouncer\Database\Role;
use Tests\TestCase;

class AttachResourceToWorkspaceTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected $user;
    protected $other_user;
    protected $org;
    protected $resource;

    protected function setUp(): void
    {
        parent::setUp();
        /*
            Create user and organization.
        */
        $this->user = User::factory()->create();
        $this->other_user = User::factory()->create();

        $this->org = Organization::factory()
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->has(Workspace::factory()->count(1))
            ->create();

        /*
            as $user, we select our workspace to null (it's interpreted as personal workspace)
        */
        $this->actingAs($this->user, 'api');
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
        ]);

        $resource
            ->assertStatus(200)
            ->assertJson([
                'id' => true
            ]);

        $this->resource = $resource->original;
    }


    public function test_userA_upload_resource_to_personal_workspace_then_attach_it_to_a_workspace_of_organization()
    {

        $this->actingAs($this->user, 'api');
        $this->setOrganization($this->user, $this->org, Roles::manager_id);

        /*
            The user must select the workspace to which he wants to attach the resource.
        */
        $wsp_user = $this->json('POST', '/api/v1/user/workspaces/select', [
            'workspace_id' => $this->org->workspaces()->where('type', WorkspaceType::generic)->first()->id,
        ]);

        $wsp_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);

        /*
            Now attach $this->resource to the user selected workspace (in this case, a generic workspace of $this->org).
        */
        $response = $this->json('POST', '/api/v1/user/resource/workspace/attach', [
            'resource_id' => $this->resource->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => true
            ]);
    }

    public function test_userB_of_the_same_organization_wants_to_view_the_resource_without_permission_and_fails()
    {
        $this->actingAs($this->other_user, 'api');
        $this->setOrganization($this->other_user, $this->org, Roles::editor_id);

        $response = $this->json('GET', '/api/v1/resource/'.$this->resource->id);

        $response
            ->assertStatus(401)
            ->assertJson([
                "read_resource_error" => "Unauthorized."
            ]);

    }

    // public function test_admin_of_organization_create_a_role_for_resources()
    // {
    //      TODO: every organization will have custom roles
    // }

    public function test_userA_create_role_with_permissions_to_view_the_resource_and_set_it_to_userB__now_userB_view_the_resource()
    {
        BouncerFacade::allow('resource-show-download')->to([
            Abilities::ShowResource,
            Abilities::DownloadResource
        ]);

        $this->actingAs($this->user, 'api');

        $role = Role::where('name', 'resource-show-download')->first();

        $response = $this->json('POST', '/api/v1/role/user/set/abilitiesOnEntity', [
            'user_id' => $this->other_user->id,
            'role_id' => $role->id,
            'entity_id' => $this->resource->id,
            'on' => Entities::resource,
            'type' => 'set',
        ]);
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => true,
                    'set_abilities' => true,
                    'on_res' => true
                ]
            ]);

        $this->actingAs($this->other_user, 'api');
        // $this->setOrganization($this->other_user, $this->org, Roles::editor_id);

        /*
            Now other_user can see the resource
        */

        $response = $this->json('GET', '/api/v1/resource/'.$this->resource->id);

        $response
            ->assertStatus(200)
            ->assertJson([
                "id" => true,
                "files" => true,
            ]);
    }

    public function test_userB_download_resource()
    {
        /*Need to assign role again, because Bouncer's bug */
        $this->actingAs($this->user, 'api');
        $role = Role::where('name', 'resource-show-download')->first();
        $response = $this->json('POST', '/api/v1/role/user/set/abilitiesOnEntity', [
            'user_id' => $this->other_user->id,
            'role_id' => $role->id,
            'entity_id' => $this->resource->id,
            'on' => Entities::resource,
            'type' => 'set',
        ]);
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => true,
                    'set_abilities' => true,
                    'on_res' => true
                ]
            ]);

        $this->actingAs($this->other_user, 'api');
        /*
            Now user B dowload resource
        */
        $res = new ResourceResource($this->resource);
        $dam_url = DamUrlUtil::generateDamUrl($res->media()->first(), $res->id);
        $response = $this->json('GET', '/api/v1/resource/download/'.$dam_url);

        $response->assertStatus(200);
    }

    // public function test_userA_create_role_with_update_permissions_for_resource_and_set_it_to_userB()
    // {
    //     //TODO
    // }
}
