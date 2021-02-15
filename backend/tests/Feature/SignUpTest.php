<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class SignUpTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_signup()
    {
        $response = $this->json('POST', '/api/v1/auth/signup', [
            'name' => 'test user',
            'email' => 'test_user'.Str::orderedUuid().'@xdam.com',
            'password'=> '123123',
            'password_confirmation'=> '123123'
        ]);

        // $response->dumpHeaders();

        // $response->dumpSession();

        // $response->dump();

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => 'Success',
                'message' => 'User Created',
                'data'=> ['access_token' => true],
            ]);
    }
}
