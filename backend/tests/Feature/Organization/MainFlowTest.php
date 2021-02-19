<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MainFlowTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_main_flow()
    {
        $admin = $this->getUserWithRole(1);
        $this->actingAs($admin, 'api');

        //set organization to user by admin
        $response = $this->json('POST', '/api/v1/organization/set/user', [
            'user_id' => '4',
            'with_role_id' => '3',
            'organization_id' => '2',
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
            'user_id' => '4',
            'with_role_id' => '3',
            'workspace_id' => '3',
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
            'user_id' => '4',
            'with_role_id' => '3',
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
