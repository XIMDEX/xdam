<?php


namespace App\Services\Amazon;

use App\Services\CDNService;
use App\Services\ResourceService;
use App\Models\DamResource;

class GetCDNResourceService
{
    private $resourceService;
    private $cdnService;

    public function __construct(ResourceService $resourceService, CDNService $cdnService)
    {
        $this->resourceService = $resourceService;
        $this->cdnService = $cdnService;
    }

    public function getResourceUrls($cdnCode, $idName)
    {
        if (($cdn = $this->cdnService->getCDNInfo($cdnCode)) === null) return response(['error' => 'The CDN doesn\'t exist.']);
        $resource = DamResource::find($idName);
        $result = [];

        if (is_null($resource)) {
            $resources = DamResource::where('name', $idName)->get();
        } else {
            $resources = collect([$resource]);
        }

        foreach ($resources as $resource) {
            if (!is_null($resource)) {
                $workspaces = $resource->workspaces()->get();
                $previews   = [];
                foreach ($workspaces as $workspace) {
                    $previews[] = ["id" => $workspace->id, "name" => $workspace->name, "url" => env('APP_URL', 'http://localhost') . "/cdn/" . $this->cdnService->encodeHash($this->cdnService->generateDamResourceHash($cdn, $resource, $resource->collection_id), $workspace->id, $resource->collection_id, false)];
                }
                $result[] = [
                    'ID' => $resource->id,
                    'nombre' => $resource->name,
                    'urls' => $previews,
                ];
            }
        }

        return $result;
    }

    public function getResourceInfo($cdnCode, $idName)
    {
        if (($cdn = $this->cdnService->getCDNInfo($cdnCode)) === null) return response(['error' => 'The CDN doesn\'t exist.']);
        $resource = DamResource::find($idName);

        $result = [];

        if (is_null($resource)) {
            $resources = DamResource::where('name', $idName)->get();
        } else {
            $resources = collect([$resource]);
        }
        foreach ($resources as $resource) {
            if (!is_null($resource)) {
                $result[] = [
                    'ID' => $resource->id,
                    'name' => $resource->name,
                    'mimeType' => $resource->type,
                    'metadata' => "{metadata}",
                ];
            }
        }

        return $result;
    }
}
