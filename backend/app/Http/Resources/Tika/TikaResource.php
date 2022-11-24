<?php

namespace App\Http\Resources\Tika;

use App\Http\Resources\Tika\{TikaMetadataResource};
use App\Services\Tika\TikaService;
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
     * @var bool $forceFiles
     */
    private bool $forceFiles;

    /**
     * @var bool $avoidDuplicates
     */
    private bool $avoidDuplicates;

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
     * @var $tikaMetadata
     */
    private $tikaMetadata;

    /**
     * @var $tikaRecursiveMetadata
     */
    private $tikaRecursiveMetadata;

    /**
     * @var $tikaLanguage
     */
    private $tikaLanguage;

    /**
     * @var $tikaMIME
     */
    private $tikaMIME;

    /**
     * @var $tikaHTML
     */
    private $tikaHTML;

    /**
     * @var $tikaXHTML
     */
    private $tikaXHTML;

    /**
     * @var $tikaText
     */
    private $tikaText;

    /**
     * @var $tikaMainText
     */
    private $tikaMainText;

    /**
     * Class constructor
     * @param $element
     * @param $elementResource
     * @param TikaService $tikaService
     * @param array $mediaInfo
     */
    public function __construct(
        $element,
        $elementResource,
        TikaService $tikaService,
        array $mediaInfo,
        bool $forceFiles,
        bool $avoidDuplicates
    ) {
        parent::__construct($element);

        $this->elementResource = $elementResource;
        $this->tikaService = $tikaService;
        $this->forceFiles = $forceFiles;
        $this->avoidDuplicates = $avoidDuplicates;

        $this->damURL = $mediaInfo['dam_url'];
        $this->mediaID = $mediaInfo['media_id'];
        $this->media = $mediaInfo['media'];
        $this->mediaPath = $mediaInfo['media_path'];
        $this->mediaFileName = $mediaInfo['media_file_name'];
        $this->mediaMIMEType = $mediaInfo['media_mime_type'];
        $this->mediaFileType = $mediaInfo['media_file_type'];

        $this->tikaMetadata = null;
        $this->tikaRecursiveMetadata = null;
        $this->tikaLanguage = null;
        $this->tikaMIME = null;
        $this->tikaHTML = null;
        $this->tikaXHTML = null;
        $this->tikaText = null;
        $this->tikaMainText = null;
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
     * @param bool $allFields
     * @return array
     */
    public function getFileMetadata(bool $allFields = true)
    {
        $metadataResource = null;

        if ($this->tikaMetadata === null) {
            $this->tikaMetadata = $this->tikaService->getFileMetadata($this->mediaPath);
        }

        if ($this->tikaMetadata !== null) {
            $tikaMetadataResource = new TikaMetadataResource($this->tikaMetadata, $this->mediaPath, $allFields);
            $metadataResource = json_encode(
                json_decode($tikaMetadataResource->toJson()), JSON_PRETTY_PRINT
            );
        }

        return $metadataResource;
    }

    /**
     * Returns the file recursive metadata
     * @return array
     */
    private function getFileRecursiveMetadata()
    {
        if ($this->tikaRecursiveMetadata === null) {
            $this->tikaRecursiveMetadata = $this->tikaService->getFileRecursiveMetadata($this->mediaPath);
        }

        return $this->tikaRecursiveMetadata;
    }

    /**
     * Returns the file language
     * @return string
     */
    private function getFileLanguage()
    {
        if ($this->tikaLanguage === null) {
            $this->tikaLanguage = $this->tikaService->getFileLanguage($this->mediaPath);
        }

        return $this->tikaLanguage;
    }

    /**
     * Returns the file MIME
     * @return string
     */
    private function getFileMIME()
    {
        if ($this->tikaMIME === null) {
            $this->tikaMIME = $this->tikaService->getFileMIME($this->mediaPath);
        }

        return $this->tikaMIME;
    }

    /**
     * Returns the file HTML content
     * @return string
     */
    public function getFileHTML()
    {
        if ($this->tikaHTML === null) {
            $this->tikaHTML = $this->tikaService->getFileHTML($this->mediaPath);
        }

        return $this->tikaHTML;
    }

    /**
     * Returns the file XHTML content
     * @return string
     */
    public function getFileXHTML()
    {
        if ($this->tikaXHTML === null) {
            $this->tikaXHTML = $this->tikaService->getFileXHTML($this->mediaPath);
        }

        return $this->tikaXHTTML;
    }

    /**
     * Returns the file text
     * @return string
     */
    public function getFileText()
    {
        if ($this->tikaText === null) {
            $this->tikaText = $this->tikaService->getFileText($this->mediaPath);
        }

        return $this->tikaText;
    }

    /**
     * Returns the file main text
     * @return string
     */
    public function getFileMainText()
    {
        if ($this->tikaMainText === null) {
            $this->tikaMainText = $this->tikaService->getFileMainText($this->mediaPath);
        }

        return $this->tikaMainText;
    }

    /**
     * Returns the Tika metadata file path
     * @return string
     */
    public function getTikaMetaFilePath()
    {
        $filePath = $this->mediaPath;
        $filePath = str_replace($this->mediaFileName, '', $filePath);
        $filePath .= ('tika_' . $this->mediaID . '.json');
        return $filePath;
    }

    /**
     * Checks if Tika is available for this file
     * @return boolean
     */
    public function isTikaAvailableForThisFile()
    {
        $mimeType = $this->getFileMIME();
        if ($mimeType === null) return false;
        return $this->tikaService->isMIMETypeSupported($mimeType);
    }

    /**
     * Returns the array content
     * @param $request
     * @return array
     */
    public function toArray($request)
    {
        if (!$this->isTikaAvailableForThisFile()) return [];
        return [
            'resource_id'               => $this->getResourceID(),
            'resource_type'             => $this->getResourceType(),
            'media_dam_url'             => $this->damURL,
            'media_id'                  => $this->mediaID,
            'media_file_name'           => $this->mediaFileName,
            'media_MIME_type'           => $this->mediaMIMEType,
            'media_file_type'           => $this->mediaFileType,
            'file_metadata'             => $this->getFileMetadata(),
            'file_recursive_metadata'   => $this->getFileRecursiveMetadata(),
            'file_language'             => $this->getFileLanguage(),
            'file_MIME'                 => $this->getFileMIME()
        ];
    }
}