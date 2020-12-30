<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Http\Requests\DeleteFileRequest;
use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Iman\Streamer\VideoStreamer;
use stdClass;

class MediaService
{
    /**
     * @var string
     */
    private $defaultFileCollection;
    /**
     * @var string
     */
    private $defaultPreviewCollection;

    /**
     * FileService constructor.
     */
    public function __construct()
    {
        $this->defaultPreviewCollection = MediaType::Preview()->value;
        $this->defaultFileCollection = MediaType::File()->value;
    }

    /**
     * @param Model $model
     * @param $collection
     * @param false $returnModel
     * @return array
     */
    public function list(Model $model, $collection, $returnModel = false)
    {
        $collection = $collection ?? $this->defaultFileCollection;
        $mediaItems = $model->getMedia($collection);
        if ($returnModel) {
            return $mediaItems;
        }
        $files = [];
        foreach ($mediaItems as $item) {
            $itemClass = new stdClass();
            $itemClass->id = $item->id;
            $itemClass->name = $item->name;
            $itemClass->url = $item->getUrl();
            $files[] = $itemClass;
        }
        return $files;
    }

    /**
     * @param Media $media
     * @return mixed
     * @throws \Exception
     */
    public function preview(Media $media)
    {
        $mimeType = $media->mime_type;
        $mediaPath = $media->getPath();
        $fileType = explode('/', $mimeType)[0];

        if ($fileType === 'video') {
            //if it is a video, render it with a streaming
            VideoStreamer::streamFile($mediaPath);
        } else {
            return $mediaPath;
        }
    }

    /**
     * @param Model $model
     * @param null $requestKey
     * @param $collection
     * @param $customProperties
     * @param null $files
     * @return array|mixed
     */
    public function addFromRequest(Model $model, $requestKey = null, $collection, $customProperties, $files = null)
    {
        $collection = $collection ?? $this->defaultFileCollection;
        if (!empty($requestKey) && empty($files))
        {
            $model->addMediaFromRequest($requestKey)->withCustomProperties($customProperties)->toMediaCollection($collection);
        }
        if (!empty($files) && empty($requestKey))
        {
            $model->addMedia($files)->withCustomProperties($customProperties)->toMediaCollection($collection);
        }
        $model->save();
        $mediaList = $this->list($model, $collection);
        return !empty($mediaList) ? end($mediaList) : [];
    }

    /**
     * @param Model $model
     * @return bool
     */
    public function deleteAllMedia(Model $model): boolean
    {
        $model->clearMediaCollection($this->defaultFileCollection);
        return true;
    }

    /**
     * @param Media $media
     * @return bool
     */
    public function deleteOne(Media $media): boolean
    {
        return $media->delete();
    }
}
