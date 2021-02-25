<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_all_collections_of_one_organization()
    {
        $admin = $this->getUserWithRole(1);

        $this->actingAs($admin, 'api');

        $response = $this->json('GET', '/api/v1/organization/1/collection/all');

        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> true,
            ]);
    }

    public function test_list_types()
    {
        $admin = $this->getUserWithRole(1);

        $this->actingAs($admin, 'api');

        $response = $this->json('GET', '/api/v1/organization/collection/types/all');

        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> true,
            ]);
    }
}
