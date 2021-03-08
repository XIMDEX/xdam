<?php

namespace Tests\Feature;

use App\Enums\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrganizationCrudTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_organization_crud_managed_only_by_super_admin()
    {
        $this->actingAs($this->getUserWithRole((new Roles)->SUPER_ADMIN_ID(), null), 'api');
        $org_name = 'Org test ' . Str::orderedUuid();

        /*
            Create
        */
        $created = $this->json('POST', '/api/v1/super-admin/organization/create', [
            'name' => $org_name,
        ]);
        $created
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['name' => true],
            ]);

        /*
            Update
        */
        $updated = $this->json('POST', '/api/v1/super-admin/organization/update', [
            'organization_id' => $created->original->id,
            'name' => $org_name . ' updated'
        ]);
        $updated
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'updated' => [
                        'id' => true,
                        'name' => $org_name . ' updated'
                        ]
                    ],
            ]);

        /*
            Delete
        */
        $deleted = $this->delete('/api/v1/super-admin/organization/' . (string)$created->original->id);
        $deleted
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'deleted' => [
                        'id' => true
                        ]
                    ],
            ]);
    }
}
