<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Collection;
use App\Models\DamResource;
use Tests\TestCase;

class UpdateAsLastUpdatedTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_update_resource_data_from_last_updated()
    {
        $super_admin = $this->getUserWithRole((new Roles)->SUPER_ADMIN_ID(), null);

        $this->actingAs($super_admin, 'api');

        $randomResource = DamResource::first();
        $collection = $randomResource->collection()->first();
        $lr = $collection->resources()->orderBy('updated_at', 'desc')->first();

        if ($lr) {
            $response = $this->json('POST', '/api/v1/resource/'.$randomResource->id.'/updateAsLastUpdated');
        }

        $rrd = $randomResource->data;
        $res = $response->getData()->data;

        $this->assertTrue($rrd == $res);
    }
}
