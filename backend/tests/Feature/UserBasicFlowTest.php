<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserBasicFlowTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_signup()
    {
        $email = 'test_user'.Str::orderedUuid().'@xdam.com';
        $user = $this->json('POST', '/api/v1/auth/signup', [
            'name' => 'test user',
            'email' => $email,
            'password'=> '123123',
            'password_confirmation'=> '123123'
        ]);

        $user
            ->assertStatus(200)
            ->assertJson([
                'status' => 'Success',
                'message' => 'User Created',
                'data'=> ['access_token' => true],
            ]);

        return ['data' => $user, 'email' => $email];
    }

    /**
    * @depends test_signup
    */
    public function test_login($user)
    {

        $res = $this->json('POST', '/api/v1/auth/login', ["email" => $user['email'], 'password'=> '123123']);

        $res
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['access_token' => true],
            ]);

        return $user['email'];
    }

    /**
    * @depends test_login
    */
    public function test_get_user($email)
    {
        $user = User::where('email', $email)->first();

        $this->actingAs($user, 'api');

        $response = $this->json('GET', '/api/v1/user');

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => ['name' => true],
            ]);

        return $response->original;
    }

    /**
    * @depends test_get_user
    */
    public function test_get_all_user_resources(User $user)
    {
        $this->actingAs($user, 'api');

        $response = $this->json('GET', '/api/v1/user/resource');

        $response->assertStatus(200);
    }

}
