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

    /**
     * Saves a resource on Amazon.
     *
     * @param string $urlFile the url of the resource.
     * @param string $nameFile the name of the resource.
     * @param string $metadata the metadata of the resource.
     * @param string $collection_id the id of the collection where the resource will be saved.
     * @param string $workspace_id the id of the workspace where the resource will be saved.
     * @param array $File the file to be saved.
     * @return DamResource the saved resource.
     *
     * @throws \Exception if the resource couldn't be saved.
     */
    public function save(String $urlFile, String $nameFile, String $metadata, string $collection_id,$type, $workspace_id, $File, $lang = 'es')
    {
        $metadata = json_decode($metadata, true);
        if (!isset($metadata['{macgh.model}isbn'])) {
            throw new \Exception('{macgh.model}isbn is required');
        }
        $isbn = $metadata['{macgh.model}isbn'] ?? null;
       
        
        $data = [
            'description' => [
                'name' => $nameFile,
                'active' => true,
                'url' => $urlFile,
                'categories' => [$isbn],
                'lang' => $lang,
                'can_download' => true
            ],
            'metadata' => $metadata ?? [],

        ];

        $params = [
            'data' => json_encode($data),
            'name' => $nameFile,
            'type' => ResourceType::fromKey($type)->value,
            'collection_id' => $collection_id,
        ];

        if ($File !== null) {
            $params['File'] = $File;
        }

        try {

            $newResource = $this->resourceService->store(
                params: $params,
                toWorkspaceId: $workspace_id,
                availableSizes: $this->getAvailableResourceSizes()
            );

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
