<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Http\Requests\DeleteFileRequest;
use App\Models\Media;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Iman\Streamer\VideoStreamer;
use Intervention\Image\Facades\Image;
use stdClass;
use Spatie\ImageOptimizer\OptimizerChainFactory;

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
        $this->defaultPreviewCollection = MediaType::Preview()->key;
        $this->defaultFileCollection = MediaType::File()->key;
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
    public function preview(Media $media, $size = null)
    {
        $mimeType = $media->mime_type;
        $mediaPath = $media->getPath();
        $fileType = explode('/', $mimeType)[0];
        $file_directory = str_replace($media->file_name, '', $mediaPath);
        $thumbnail = $file_directory . '/' . $media->filename . '__thumb_.png';

        if ($fileType === 'video') {

            if($size === 'raw') {
                return VideoStreamer::streamFile($mediaPath);
            }

            $thumb_exists = File::exists($thumbnail);
            if(!$thumb_exists) {
                $sec = 10;
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries'  => config("FFMPEG_BIN_PATH"),
                    'ffprobe.binaries' => config("FFPROBE_BIN_PATH")
                ]);
                $video = $ffmpeg->open($mediaPath);
                $frame = $video->frame(TimeCode::fromSeconds($sec));
                $frame->save($thumbnail);
            } else {
                return Image::make($thumbnail);
            }
            //USE THIS PACKAGE TO RENDER THE VIDEO

        } else {
            return Image::make($mediaPath);


            //return $mediaPath;
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
     * @param $collection
     * @return bool
     */
    public function deleteAllPreviews(Model $model)
    {
        return $model->clearMediaCollection($this->defaultPreviewCollection);
    }

    /**
     * @param Model $model
     * @param $collection
     * @return bool
     */
    public function deleteAllFiles(Model $model)
    {
        return $model->clearMediaCollection($this->defaultFileCollection);
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
