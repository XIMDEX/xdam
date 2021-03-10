<?php

namespace Tests\Feature;

use App\Enums\Abilities;
use App\Enums\Entities;
use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Http\Resources\ResourceResource;
use App\Models\Ability;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use App\Utils\DamUrlUtil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use Tests\TestCase;

class AttachResourceToWorkspaceTest extends TestCase
{
    // use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_a_upload_a_resource_on_a_corporate_wsp_in_multimedia_default_collection()
    {
        /*
            Create user and organization.
        */
        $user = User::factory()->create();
        $other_user = User::factory()->create();

        $org = Organization::factory()
            ->has(Workspace::factory(['type' => WorkspaceType::corporate])->count(1))
            ->has(Workspace::factory(['type' => WorkspaceType::generic])->count(2))
            ->create();

        $this->setOrganization($user, $org, Roles::admin_id, false);

        $this->setOrganization($other_user, $org, Roles::reader_id);

        /*
        as $user, we select the corporate workspace of the organization
        */
        $this->actingAs($user, 'api');

        $wsp_user = $this->json('POST', '/api/v1/user/workspaces/select', [
            'workspace_id' => $org->corporateWorkspace()->id,
        ]);

        $wsp_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);
        /*
            Now, upload the resource to the $user->selected_workspace
        */

        Storage::fake('avatars');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $resource = $this->json('POST', '/api/v1/resource', [
            'File' => [$file],
            'type' => 'image',
            'name' => 'imagen test',
            'data' => '{"description": {"active": true, "partials": {"pages": 10}}}',
            'collection_id' => $org->collections()->get()[0]->id //a default multimedia collection
        ]);

        $resource
            ->assertStatus(200)
            ->assertJson([
                'id' => true
            ]);

        $resource = $resource->original;

        return [
            'org' => $org,
            'user' => $user,
            'other_user' => $other_user,
            'resource' => $resource,
        ];
    }


    /**
    * @depends test_user_a_upload_a_resource_on_a_corporate_wsp_in_multimedia_default_collection
    */
    public function test_user_a_attach_the_resource_to_a_generic_workspace_of_organization(array $data)
    {
        $this->actingAs($data['user'], 'api');
        /*
            First select the generic Workspace
        */
        $wsp_user = $this->json('POST', '/api/v1/user/workspaces/select', [
            'workspace_id' => $data['org']->firstGenericWorkspace()->id,
        ]);


        $wsp_user
            ->assertStatus(200)
            ->assertJson([
                'data'=> true
            ]);

        /*
            Now attach the $resource which is in corporate to the new user selected workspace (in this case, a generic workspace of $org).
        */
        $response = $this->json('POST', '/api/v1/user/resource/workspace/attach', [
            'resource_id' => $data['resource']->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => true
            ]);
    }

    /**
    * @depends test_user_a_upload_a_resource_on_a_corporate_wsp_in_multimedia_default_collection
    */
    public function test_user_b_of_the_same_organization_can_view_the_resource_beacuse_it_is_in_corporate_workspace(array $data)
    {
        $this->actingAs($data['other_user'], 'api');

        /*
            Need's to select the corporate workspace first, else get a Unauthorized error
        */
        $data['other_user']->selected_workspace = $data['org']->corporateWorkspace()->id;
        $data['other_user']->save();

        $response = $this->json('GET', '/api/v1/resource/'.$data['resource']->id);

        $response
            ->assertStatus(200)
            ->assertJson([
                "id" => true
            ]);

    }


    /**
    * @depends test_user_a_upload_a_resource_on_a_corporate_wsp_in_multimedia_default_collection
    */
    public function test_user_b_of_the_same_organization_wants_to_view_the_resource_without_permission_in_the_generic_workspace_and_fails(array $data)
    {
        $this->actingAs($data['other_user'], 'api');

        /*
            Select the generic Workspace, at this moment $other_user has not permissions to read/view the workspace
        */
        $data['other_user']->selected_workspace = $data['org']->firstGenericWorkspace()->id;
        $data['other_user']->save();

        $response = $this->json('GET', '/api/v1/resource/'.$data['resource']->id);

        $response
            ->assertStatus(401)
            ->assertJson([
                "read_workspace_error" => "Unauthorized."
            ]);

    }

    /**
    * @depends test_user_a_upload_a_resource_on_a_corporate_wsp_in_multimedia_default_collection
    */
    public function test_user_a_create_a_role_with_permissions_to_view_resources_in_the_generic_workspace(array $data)
    {
        $this->actingAs($data['user'], 'api');
        /*
            Create Role
        */

        $role = $this->json('POST', '/api/v1/organization/'.$data['org']->id.'/roles/store', [
            'name' => 'role-show-resources',
        ]);


        $role
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['name' => true],
            ]);


        /*
            Set abilities to role
        */

        $role_with_ability = $this->json('POST', '/api/v1/organization/'.$data['org']->id.'/roles/set/ability', [
            'role_id' => $role->original->id,
            'ability_ids' => [
                Ability::where(['name' => Abilities::READ_RESOURCE, 'entity_type' => null ])->first()->id,
                Ability::where(['name' => Abilities::DOWNLOAD_RESOURCE, 'entity_type' => null ])->first()->id
            ]
        ]);
        $role_with_ability
            ->assertStatus(200)
            ->assertJson([
                'data'=> [],
            ]);
        $role = $role_with_ability->original['role'];

        return [
            'role' => $role,
            'other_user' => $data['other_user'],
            'user' => $data['user'],
            'org' => $data['org'],
            'resource' => $data['resource']
        ];
    }

    /**
    * @depends test_user_a_create_a_role_with_permissions_to_view_resources_in_the_generic_workspace
    */
    public function test_user_a_attach_user_b_to_generic_workspace_and_set_created_role(array $data)
    {
        $this->actingAs($data['user'], 'api');

        /*
            First Attach other_user to workspace
        */

        $response = $this->json('POST', '/api/v1/workspace/set/user', [
            'user_id' => $data['other_user']->id,
            'with_role_id' => Roles::reader_id,
            'workspace_id' => $data['org']->firstGenericWorkspace()->id
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => true,
                ]
            ]);

        /*
            Set created role to other_user, so user can show and download resources inside the wsp
        */

        $response = $this->json('POST', '/api/v1/role/user/set/abilitiesOnEntity', [
            'user_id' => $data['other_user']->id,
            'role_id' => $data['role']->id,
            'entity_id' => $data['org']->firstGenericWorkspace()->id,
            'on' => Entities::workspace,
            'type' => 'set',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => true,
                ]
            ]);


        return [
            'org' => $data['org'],
            'user' => $data['user'],
            'other_user' => $data['other_user'],
            'resource' => $data['resource']
        ];
    }

    /**
    * @depends test_user_a_attach_user_b_to_generic_workspace_and_set_created_role
    */
    public function test_userB_now_view_workspace_the_resource_and_download_it(array $data_updated)
    {
        /*
            $other_user must select the generic workspace, where now has permissions to see the workspace &
        */

        $this->actingAs($data_updated['other_user'], 'api');
        $data_updated['other_user']->selected_workspace = $data_updated['org']->firstGenericWorkspace()->id;

        /*
            Now other_user can see the resource
        */

        $response = $this->json('GET', '/api/v1/resource/'.$data_updated['resource']->id);

        $response
            ->assertStatus(200)
            ->assertJson([
                "id" => true,
                "files" => true,
            ]);


        /*
            Now user B dowload resource
        */
        $res = new ResourceResource($data_updated['resource']);
        $dam_url = DamUrlUtil::generateDamUrl($res->media()->first(), $res->id);
        $response = $this->json('GET', '/api/v1/resource/download/'.$dam_url);

        $response->assertStatus(200);
    }

}
