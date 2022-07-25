<?php

namespace App\Services;
use App\Enums\MediaType;
use App\Models\Media;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Iman\Streamer\VideoStreamer;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Imagine;
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
            return $this->previewVideo($media->file_name, $mediaPath, $size, $thumbnail);
        } else if($fileType === 'image') {
            return $this->previewImage($mediaPath, $size);
        } else {
            return $mediaPath;
        }
    }

    private function previewVideo($mediaFileName, $mediaPath, $size = null, $thumbnail = null)
    {
        if ($size == 'thumbnail') {
            $thumb_exists = File::exists($thumbnail);

            if (!$thumb_exists) {
                $this->saveVideoSnapshot($thumbnail, $mediaPath);
            } else {
                return Image::make($thumbnail);
            }
        } else if ($size == 'raw') {
            return VideoStreamer::streamFile($mediaPath);
        } else {
            $fileDirectory = implode('/', array_slice(explode('/', $mediaPath), 0, -1))
                                . '/' . pathinfo($mediaFileName, PATHINFO_FILENAME) . '_'
                                . $size . '.' . pathinfo($mediaFileName, PATHINFO_EXTENSION);
            $relFileDirectory = str_replace(storage_path('app') . '/', '', $fileDirectory);

            if (!file_exists($fileDirectory)) {
                $command = "ffmpeg -i $mediaPath -vf scale=256:144 -preset slow -crf 18 $fileDirectory";
                exec($command);
            }
                return $this->previewVideo($mediaFileName, $mediaPath, 'raw', $thumbnail);

            return
                [
                    Storage::disk('local')->path($relFileDirectory),
                    [
                        'Content-Type'  => 'application/vnd.apple.mpegURL',
                        'isHls'         => true
                    ]
                ];
        }

        return $mediaPath;
    }

    private function previewImage($mediaPath, $size)
    {
        $manager = new ImageManager(['driver' => 'imagick']);
        $image = $manager->make($mediaPath);

        if ($size !== 'raw') {
            $width = $image->width();
            $height = $image->height();
            $aspectRatio = $width / $height;

            if ($size >= $height) return $image;

            if ($aspectRatio >= 1.0) {
                $newHeight = $height * $size / 100;
                $newWidth = $newHeight / $aspectRatio;
            } else {
                $newWidth = $width * $size / 100;
                $newHeight = $newWidth * $aspectRatio;
            }
    
            $image->resize($newHeight, $newWidth);
        }

        return $image;
    }

    public function saveVideoSnapshot($thumbPath, $videoSourcePath)
    {
        $sec = 10;
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => config('app.ffmpeg_path'),
            'ffprobe.binaries' => config('app.ffprobe_path')
        ]);
        $video = $ffmpeg->open($videoSourcePath);
        $frame = $video->frame(TimeCode::fromSeconds($sec));
        $frame->save($thumbPath);
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

        $model = $model->fresh();

        $mediaList = $this->list($model, $collection);

        $media = Media::findOrFail($mediaList[0]->id);
        $mimeType = $media->mime_type;
        $mediaPath = $media->getPath();
        $fileType = explode('/', $mimeType)[0];
        if($fileType == 'video') {
            $file_directory = str_replace($media->file_name, '', $mediaPath);
            $thumbnail = $file_directory . '/' . $media->filename . '__thumb_.png';
            $this->saveVideoSnapshot($thumbnail, $mediaPath);
        }
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
