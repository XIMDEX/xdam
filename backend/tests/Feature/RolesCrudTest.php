<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RolesCrudTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_roles_crud_managed_only_by_admin()
    {
        $this->actingAs($this->getUserWithRole(1), 'api');


        /*
        /
        /CREATE ROLE
        /
        */


        $created = $this->json('POST', '/api/v1/super-admin/role/store', [
            'name' => 'role-technical-name',
            'title' => 'Role semantic name'
        ]);
        $created
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['name' => true],
            ]);

    }
}
