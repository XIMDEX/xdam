<?php

namespace App\Services\Tika;

use App\Http\Resources\ResourceResource;
use App\Http\Resources\Tika\TikaResource;
use App\Models\{DamResource, Media};
use App\Services\Tika\TikaService;
use App\Utils\DamUrlUtil;

class TikaResourceService
{
    /**
     * @var TikaService $tikaService
     */
    private TikaService $tikaService;

    /**
     * Class constructor
     * @param TikaService $tikaService
     */
    public function __construct(TikaService $tikaService)
    {
        $this->tikaService = $tikaService;
    }

    private function getMediaInfo(string $damURL)
    {
        $mediaID = -1000;
        $media = null;
        $mediaPath = '';
        $mediaFileName = '';
        $mimeType = '';
        $fileType = '';

        try {
            $mediaID = DamUrlUtil::decodeUrl($damURL);
            $media = Media::findOrFail($mediaID);
            $mediaPath = $media->getPath();
            $mediaFileName = explode('/', $mediaPath);
            $mediaFileName = $mediaFileName[count($mediaFileName) - 1];
            $mimeType = $media->mime_type;
            $fileType = explode('/', $mimeType)[0];
        } catch (\Exception $ex) {
            // echo $ex->getMessage();
        }

        return [
            'dam_url'           => $damURL,
            'media_id'          => $mediaID,
            'media'             => $media,
            'media_path'        => $mediaPath,
            'media_file_name'   => $mediaFileName,
            'media_mime_type'   => $mimeType,
            'media_file_type'   => $fileType
        ];
    }

    private function writeTikaData($tikaJSONData, TikaResource $tikaData)
    {
        $status = false;

        try {
            $file = fopen($tikaData->getTikaMetaFilePath(), 'w');
            fwrite($file, $tikaJSONData);
            fclose($file);
            $status = true;
        } catch (\Exception $ex) {
            // echo $ex->getMessage();
            $status = false;
        }

        return $status;
    }

    private function getMediaFileInfo(
        DamResource $damResource,
        $resourceResource,
        array $mediaInfo,
        bool $forceFiles,
        bool $avoidDuplicates
    ) {
        $tikaResource = new TikaResource($damResource, $resourceResource,
                                            $this->tikaService, $mediaInfo,
                                            $forceFiles, $avoidDuplicates);
        if (!$tikaResource->isTikaAvailableForThisFile()) return null;
        $tikaResourceJSON = json_encode(json_decode($tikaResource->toJson()), JSON_PRETTY_PRINT);
        if (!$this->writeTikaData($tikaResourceJSON, $tikaResource)) return null;
        return $tikaResource;
    }

    /**
     * Returns the media attached to a resource
     * @param DamResource $damResource
     * @param boolean $forceFiles
     * @param boolean $avoidDuplicates
     * @return array
     */
    public function getMediaAttached(
        DamResource $damResource,
        bool $forceFiles,
        bool $avoidDuplicates,
        int $attempt = 0
    ): array {
        $files = [];

        try {
            $resourceResource = new ResourceResource($damResource);
            $resourceJSON = json_decode($resourceResource->toJson());
            $auxFiles = $resourceJSON->files;
    
            foreach ($auxFiles as $item) {
                $mediaInfo = $this->getMediaInfo($item->dam_url);
    
                if ($mediaInfo['media'] !== null) {
                    $aux = $this->getMediaFileInfo($damResource, $resourceResource, $mediaInfo,
                                                    $forceFiles, $avoidDuplicates);
                    if ($aux !== null) $files[] = $aux;
                }
            }
        } catch (\Exception $ex) {
            // echo $ex->getMessage();

            if ($attempt < 15) {
                sleep(1);
                $files = $this->getMediaAttached($damResource, $forceFiles, $avoidDuplicates, $attempt + 1);
            }
        }

        return $files;
    }
}