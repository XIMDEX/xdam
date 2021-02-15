<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetUserTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_user()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $response = $this->json('GET', '/api/v1/user');

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => ['name' => true],
            ]);
    }
}
