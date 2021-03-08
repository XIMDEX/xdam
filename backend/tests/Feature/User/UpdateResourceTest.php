<?php

namespace Tests\Feature;

use App\Http\Resources\ResourceResource;
use App\Models\User;
use App\Utils\DamUrlUtil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateResourceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_a_basic_user_render_resource_of_public_workspace()
    {
        $basic_user = User::where('email', 'basic_user@xdam.com')->first();

        $org = $basic_user->organizations()->where('type', 'public')->first();

        $resource_in_public_wsp = $org->collections()->where('solr_connection', 'multimedia')->first()->resources()->get()[0];

        $this->actingAs($basic_user, 'api');

        $res = new ResourceResource($resource_in_public_wsp);
        $dam_url = DamUrlUtil::generateDamUrl($res->media()->first(), $res->id);

        $resource = $this->json('GET', '/api/v1/resource/render/'.$dam_url);

        $resource->assertStatus(200);

    }
}
