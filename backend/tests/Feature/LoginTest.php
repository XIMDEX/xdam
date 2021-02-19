<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_login()
    {
        $response = $this->json('POST', '/api/v1/auth/login', ["email" => 'admin@xdam.com', 'password'=> '123123']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['access_token' => true],
            ]);
    }
}
