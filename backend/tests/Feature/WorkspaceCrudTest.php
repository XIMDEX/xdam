<?php

namespace Tests\Feature;

use App\Enums\Abilities;
use App\Models\Organization;
use App\Models\User;
use Error;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WorkspaceCrudTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_workspace_crud()
    {
        $this->actingAs($this->getUser(null, [Abilities::canManageWorkspace]), 'api');

        $org = Organization::factory()->create();

        $created = $this->json('POST', '/api/v1/workspace/create', [
            'organization_id' => $org->id,
            'name' => $org->name . ' - Workspace'
        ]);
        $created
            ->assertStatus(200)
            ->assertJson([
                'data'=> ['name' => true],
            ]);

        $updated = $this->json('POST', '/api/v1/workspace/update', [
            'workspace_id' => $created->original->id,
            'name' => $org->name . ' - Workspace - updated'
        ]);
        $updated
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'updated' => [
                        'id' => true,
                        'name' => $org->name . ' - Workspace - updated'
                        ]
                    ],
            ]);

        $deleted = $this->delete('/api/v1/workspace/' . (string)$created->original->id);
        $deleted
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'deleted' => [
                        'id' => true
                        ]
                    ],
            ]);
    }
}
