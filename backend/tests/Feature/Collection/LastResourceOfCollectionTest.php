<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Collection;
use App\Models\DamResource;
use Tests\TestCase;

class LastResourceOfCollectionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_should_return_lastest_resource_of_collection()
    {
        $super_admin = $this->getUserWithRole((new Roles)->SUPER_ADMIN_ID(), null);

        $this->actingAs($super_admin, 'api');

        $lastResource = null;
        $response = null;

        $collection = Collection::all();

        foreach ($collection as $coll) {
            $lr = $coll->resources()->orderBy('created_at', 'desc')->first();
            if ($lr) {
                $lastResource = $lr->toArray();
                $response = $this->json('GET', '/api/v1/resource/lastCreated/'.$coll->id);
                break;
            }
        }

        $this->assertTrue($lastResource['id'] === $response->getData()->id);

    }
}
