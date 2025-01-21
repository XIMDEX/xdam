<?php

namespace App\Services\Amazon;

use App\Enums\ResourceType;
use App\Services\ResourceService;
use Illuminate\Support\Facades\Auth;

class SaveAmazonResourceService
{
    private $resourceService;

    public function __construct(ResourceService $resourceService)
    {
        $this->resourceService = $resourceService;
    }

    public function save(String $urlFile, String $nameFile, $metadata,string $collection_id, $txtFile)
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
            'type' => ResourceType::fromKey('image')->value,
            'collection_id' => $collection_id,
        ];
        if ($txtFile !== null) {
            $params['File'] = $txtFile;
        }

        try {

            $newResource = $this->resourceService->store(params: $params,toWorkspaceId: 24,
            availableSizes: $this->getAvailableResourceSizes());
            return $newResource;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    private function getAvailableResourceSizes()
    {
        $sizes = [
            'image' => [
                'allowed_sizes' => ['thumbnail', 'small', 'medium', 'large', 'raw', 'default'],
                'sizes' => [
                    'thumbnail' => array('width' => 256, 'height' => 144),
                    'small'     => array('width' => 426, 'height' => 240),
                    'medium'    => array('width' => 1280, 'height' => 720),
                    'large'     => array('width' => 1920, 'height' => 1080), //4k 3840x2160 HD 1920x1080
                    'raw'       => 'raw',
                    'default'   => array('width' => 1280, 'height' => 720)
                ],
                'qualities' => [
                    'thumbnail' => 25,
                    'small'     => 25,
                    'medium'    => 50,
                    'large'     => 100,
                    'raw'       => 'raw',
                    'default'   => 90,
                ],
                'error_message' => ''
            ],
            'video' => [
                'allowed_sizes' => ['very_low', 'low', 'standard', 'hd', 'raw', 'thumbnail', 'small', 'medium', 'default'],
                'sizes_scale'   => ['very_low', 'low', 'standard', 'hd'],   // Order Lowest to Greatest
                'screenshot_sizes'  => ['thumbnail', 'small', 'medium'],
                'sizes' => [
                    // 'lowest'        => array('width' => 256, 'height' => 144, 'name' => '144p'),
                    'very_low'      => array('width' => 426, 'height' => 240, 'name' => '240p'),
                    'low'           => array('width' => 640, 'height' => 360, 'name' => '360p'),
                    'standard'      => array('width' => 854, 'height' => 480, 'name' => '480p'),
                    'hd'            => array('width' => 1280, 'height' => 720, 'name' => '720p'),
                    // 'full_hd'       => array('width' => 1920, 'height' => 1080, 'name' => '1080p'),
                    'raw'           => 'raw',
                    //'thumbnail'     => 'thumbnail',
                    'thumbnail'     => array('width' => 256, 'height' => 144, 'name' => '144p'),
                    'small'         => array('width' => 426, 'height' => 240, 'name' => '240p'),
                    'medium'        => array('width' => 854, 'height' => 480, 'name' => '480p'),
                    'default'       => 'raw'
                ],
                'qualities' => [
                    'thumbnail' => 25,
                    'small'     => 25,
                    'medium'    => 50,
                    'raw'       => 'raw',
                    'default'   => 90
                ],
                'error_message' => ''
            ]
        ];


        return $sizes;
    }
}
