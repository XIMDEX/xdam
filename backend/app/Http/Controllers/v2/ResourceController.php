<?php
declare(strict_types=1);

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resource\GetResourcesSchemaRequest;
use App\Services\ResourceService;

final class ResourceController extends Controller
{

    private ResourceService $resourceService;

    public function __construct(ResourceService $resourceService)
    {
        $this->resourceService = $resourceService;
    }

    public function getSchemas(GetResourcesSchemaRequest $request) {
        $schemas = $this->resourceService->resourcesSchema();
        return response()->json($schemas);
    }
}