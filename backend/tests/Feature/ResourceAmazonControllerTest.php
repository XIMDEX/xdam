<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Workspace;
use App\Services\CategoryService;
use App\Services\Amazon\AssignWorkspaceService;
use Tests\TestCase;
use App\Models\User;

use Mockery;


class ResourceAmazonControllerTest extends TestCase
{

    protected $categoryService;
    protected $assignWorkspaceService;
    

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = Mockery::mock(CategoryService::class);
        $this->assignWorkspaceService = Mockery::mock(AssignWorkspaceService::class);
        $this->app->instance(CategoryService::class, $this->categoryService);
        $this->app->instance(AssignWorkspaceService::class, $this->assignWorkspaceService);
    }

    public function test_assign_workspace_success()
    {
        $user = User::factory()->make();
        // Arrange
        $workspace = Workspace::factory()->create();
        $category = Category::factory()->create(['name' => '1234567890']);
        $resources = ['resource1', 'resource2'];
      
        $this->categoryService->shouldReceive('getResources')->once()->andReturn($resources);
        $this->assignWorkspaceService->shouldReceive('assignWorkspace')->once();

        // Act
        $response = $this->actingAs($user,'api') // Authenticate the user
        ->postJson(route('v1damResource.assign.workspace', [
            'isbn' => $category->name,
            'workspace' => $workspace->id
        ]));

        // Assert
        $response->assertStatus(200)
            ->assertJson(['message' => "Resources assigned to workspace {$workspace->id} successfully"]);
    }

    public function test_assign_workspace_nonexistent_workspace()
    {
        $user = User::factory()->make();
        // Act
        $response = $this->actingAs($user,'api')->postJson(route('v1damResource.assign.workspace', [
            'isbn' => '1234567890',
            'workspace' => 999 // Non-existent workspace ID
        ]));

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => "The workspace doesn't exist."]);
    }

    public function test_assign_workspace_nonexistent_isbn()
    {
        $user = User::factory()->make();
        // Arrange
        $workspace = Workspace::factory()->create();

        // Act
        $response = $this->actingAs($user,'api')->postJson(route('v1damResource.assign.workspace', [
            'isbn' => '9999999999', // Non-existent ISBN
            'workspace' => $workspace->id
        ]));

        // Assert
        $response->assertStatus(404)
            ->assertJson(['error' => "The ISBN doesn't exist."]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}