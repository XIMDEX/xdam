<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Silber\Bouncer\BouncerFacade;
use Tests\TestCase;

class AddResourceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_add_resource()
    {
        $user = $this->getUserWithRole(2);

        $this->actingAs($user, 'api');

        Storage::fake('avatars');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->json('POST', '/api/v1/resource', [
            'File' => [$file],
            'type' => 'image',
            'data' => '{"description": {"active": true, "partials": {"pages": 10}}}'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => true
            ]);
    }
}
