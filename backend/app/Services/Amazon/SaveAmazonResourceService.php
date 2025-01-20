<?php

namespace App\Services\Amazon;

use App\Enums\ResourceType;
use App\Services\ResourceService;

class SaveAmazonResourceService
{
    private $resourceService;

    public function __construct(ResourceService $resourceService)
    {
        $this->resourceService = $resourceService;
    }

    public function save(String $urlFile, String $nameFile, $metadata, $txtFile)
    {
        $params = [
            'data' => json_encode([
                'description' => [
                    'name' => $nameFile,
                    'active' => true,
                    'url' => $urlFile,
                ],
            'metadata' => $metadata ?? []
            ]),
            'name' => $nameFile,
            'type' => ResourceType::fromKey('multimedia')->value,
        ];
        if ($txtFile !== null) {
            $params['File'] = $txtFile;
        }

        try {
            $newResource = $this->resourceService->store($params,'4');
            return $newResource;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
