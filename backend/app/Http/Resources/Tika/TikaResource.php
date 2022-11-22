<?php

namespace App\Http\Resources\Tika;

use App\Services\TikaService;
use Illuminate\Http\Resources\Json\JsonResource;

class TikaResource extends JsonResource
{
    /**
     * @var $elementResource
     */
    private $elementResource;

    /**
     * @var TikaService $tikaService
     */
    private TikaService $tikaService;

    /**
     * @var string $damURL
     */
    private string $damURL;

    /**
     * @var $mediaID
     */
    private $mediaID;

    /**
     * @var $media
     */
    private $media;

    /**
     * @var string $mediaPath
     */
    private string $mediaPath;

    /**
     * @var string $mediaFileName
     */
    private string $mediaFileName;

    /**
     * @var string $mediaMIMEType
     */
    private string $mediaMIMEType;

    /**
     * @var string $mediaFileType
     */
    private string $mediaFileType;

    /**
     * Constructor
     * @param $element
     * @param $elementResource
     * @param TikaService $tikaService
     * @param array $mediaInfo
     */
    public function __construct(
        $element,
        $elementResource,
        TikaService $tikaService,
        array $mediaInfo
    ) {
        parent::__construct($element);
        $this->elementResource = $elementResource;
        $this->tikaService = $tikaService;

        $this->damURL = $mediaInfo['dam_url'];
        $this->mediaID = $mediaInfo['media_id'];
        $this->media = $mediaInfo['media'];
        $this->mediaPath = $mediaInfo['media_path'];
        $this->mediaFileName = $mediaInfo['media_file_name'];
        $this->mediaMIMEType = $mediaInfo['media_mime_type'];
        $this->mediaFileType = $mediaInfo['media_file_type'];
    }

    private function getResourceID()
    {
        return $this->id;
    }

    /**
     * Returns the resource type
     * @return string
     */
    private function getResourceType()
    {
        return $this->type;
    }

    /**
     * Returns the file metadata
     * @return array
     */
    private function getFileMetadata()
    {
        return $this->tikaService->getFileMetadata($this->mediaPath);
    }

    /**
     * Returns the file recursive metadata
     * @return array
     */
    private function getFileRecursiveMetadata()
    {
        return $this->tikaService->getFileRecursiveMetadata($this->mediaPath);
    }

    /**
     * Returns the file language
     * @return string
     */
    private function getFileLanguage()
    {
        return $this->tikaService->getFileLanguage($this->mediaPath);
    }

    /**
     * Returns the file MIME
     * @return string
     */
    private function getFileMIME()
    {
        return $this->tikaService->getFileMIME($this->mediaPath);
    }

    /**
     * Returns the file HTML content
     * @return string
     */
    public function getFileHTML()
    {
        return $this->tikaService->getFileHTML($this->mediaPath);
    }

    /**
     * Returns the file XHTML content
     * @return string
     */
    public function getFileXHTML()
    {
        return $this->tikaService->getFileXHTML($this->mediaPath);
    }

    /**
     * Returns the file text
     * @return string
     */
    public function getFileText()
    {
        return $this->tikaService->getFileText($this->mediaPath);
    }

    /**
     * Returns the Tika metadata file path
     * @return string
     */
    public function getTikaMetaFilePath()
    {
        $filePath = $this->mediaPath;
        $filePath = str_replace($this->mediaFileName, '', $filePath);
        $filePath .= ('tika_' . $this->getMediaId() . '.json');
        return $filePath;
    }

    /**
     * Returns the array content
     * @param $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'resource_id'               => $this->getResourceID(),
            'resource_type'             => $this->getResourceType(),
            'media_dam_url'             => $this->damURL,
            'media_id'                  => $this->mediaID,
            'media_file_name'           => $this->mediaFileName,
            'file_metadata'             => $this->getFileMetadata(),
            'file_recursive_metadata'   => $this->getFileRecursiveMetadata(),
            'file_language'             => $this->getFileLanguage(),
            'file_MIME'                 => $this->getFileMIME()
        ];
    }
}