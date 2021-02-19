<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetAllUserResourcesTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_all_user_resources()
    {
        $this->actingAs($this->getUserWithRole(2), 'api');

        $response = $this->json('GET', '/api/v1/user/resources');

        $response
            ->assertStatus(200);
    }
}
