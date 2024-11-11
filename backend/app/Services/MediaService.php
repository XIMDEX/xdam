<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Jobs\ProcessVideoCompression;
use App\Models\DocumentRendererKey;
use App\Models\Media;
use App\Models\PendingVideoCompressionTask;
use App\Services\Media\MediaSizeImage;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Iman\Streamer\VideoStreamer;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use App\Utils\DamUrlUtil;
use Imagine;
use Iman\Streamer\Video;
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
    public function preview(Media $media, $availableSizes, $sizeKey = null, $size = null, $isDownload = false)
    {
        $mimeType = $media->mime_type;
        $mediaPath = $media->getPath();
        $fileType = explode('/', $mimeType)[0];
        $file_directory = str_replace($media->file_name, '', $mediaPath);
        $thumbnail = $file_directory . '/' . $media->filename . '__thumb_.png';
        if ($fileType === 'video') {
            return $isDownload
                ? $this->downloadVideo($media->id, $media->file_name, $mediaPath, $availableSizes, $sizeKey, $size, $thumbnail)
                : $this->previewVideo($media->id, $media->file_name, $mediaPath, $availableSizes, $sizeKey, $size, $thumbnail);
        } else if ($fileType === 'image') {
            return $this->previewImage($mediaPath, $sizeKey);
        } else {
            return $mediaPath;
        }
    }

    private function getVideoDimensions($path)
    {
        $command = "ffmpeg -i \"$path\" 2>&1 | grep Video: | grep -Po '\d{3,5}x\d{3,5}'";
        $output = explode('x', exec($command));
        $resolution = array(
            "path" => $path,
            "width" => $output[0],
            "height" => $output[1],
            "aspect_ratio" => $output[0] / $output[1],
            "name" => $output[1] . "p"
        );
        return $resolution;
    }

    private function updateAvailableSizes(&$availableSizes, $aspectRatio, $mediaPath, $mediaFileName)
    {
        foreach ($availableSizes['sizes_scale'] as $k) {
            if ($aspectRatio >= 1) { // Horizontal
                $availableSizes['sizes'][$k]['width'] = ceil($availableSizes['sizes'][$k]['height'] * $aspectRatio);
            } else { // Vertical
                $availableSizes['sizes'][$k]['width'] = ceil($availableSizes['sizes'][$k]['height'] / $aspectRatio);
            }

            if ($availableSizes['sizes'][$k]['width'] % 2 !== 0) $availableSizes['sizes'][$k]['width'] -= 1;

            $availableSizes['sizes'][$k]['path'] = implode('/', array_slice(explode('/', $mediaPath), 0, -1))
                . '/' . pathinfo($mediaFileName, PATHINFO_FILENAME) . '_'
                . $availableSizes['sizes'][$k]['name'] . '.'
                . pathinfo($mediaFileName, PATHINFO_EXTENSION);
            $availableSizes['sizes'][$k]['relative_path'] = str_replace(storage_path('app') . '/', '', $availableSizes['sizes'][$k]['path']);
        }
    }

    private function getValidSizesRange($availableSizes, $originalResolution)
    {
        $sizesRange = [];

        foreach ($availableSizes['sizes_scale'] as $k) {
            if ($availableSizes['sizes'][$k]['height'] < $originalResolution['height']) {
                $sizesRange[$k] = $availableSizes['sizes'][$k];
            }
        }

        return $sizesRange;
    }

    private function downloadVideo($mediaID, $mediaFileName, $mediaPath, $availableSizes, $sizeKey = null, $size = null, $thumbnail = null)
    {
        return $this->getVideo($mediaID, $mediaFileName, $mediaPath, $availableSizes, $sizeKey, $size, $thumbnail, true);
        $video = new Video();
        $video->setPath($mediaPath);
        return $video;
    }

    private function previewVideo($mediaID, $mediaFileName, $mediaPath, $availableSizes, $sizeKey = null, $size = null, $thumbnail = null)
    {
        return $this->getVideo($mediaID, $mediaFileName, $mediaPath, $availableSizes, $sizeKey, $size, $thumbnail, false);
    }


    private function getVideo($mediaID, $mediaFileName, $mediaPath, $availableSizes, $sizeKey = null, $size = null, $thumbnail = null, $isDownload = false)
    {
        // Gets the real resolution of the video, and updates the available resolutions, according the computed aspect ratio
        $originalRes = $this->getVideoDimensions($mediaPath);
        $this->updateAvailableSizes($availableSizes, $originalRes['aspect_ratio'], $mediaPath, $mediaFileName);

        // Gets the available sizes, with the valid range for this current file
        $validSizes = $this->getValidSizesRange($availableSizes, $originalRes);

        if ($size == 'thumbnail' || in_array($sizeKey, $availableSizes['screenshot_sizes'])) {
            $thumb_exists = File::exists($thumbnail);

            if (!$thumb_exists) {
                $this->saveVideoSnapshot($thumbnail, $mediaPath);
            } else {
                return $this->previewImage($thumbnail, $size);
            }
        } else if ($size == 'raw') {
            return $this->getPreviewOrDownload($mediaPath, $isDownload);
        } else {
            if (!array_key_exists($sizeKey, $validSizes)) {
                return $this->getVideo($mediaID, $mediaFileName, $mediaPath, $availableSizes, $sizeKey, 'raw', $thumbnail, $isDownload);
            }
            if (!file_exists($validSizes[$sizeKey]['path'])) {
                $task = (object)([
                    'media_id' => $mediaID,
                    'resolution' => $validSizes[$sizeKey]['width'] . ':' . $validSizes[$sizeKey]['height'],
                    'src_path' => $mediaPath,
                    'dest_path' => $validSizes[$sizeKey]['path'],
                    'media_conversion_name_id' => $validSizes[$sizeKey]['name']
                ]);
                ProcessVideoCompression::dispatch($task);

                $validSizesKeys = array_keys($validSizes);

                for ($i = count($validSizesKeys) - 1; $i >= 0; $i--) {
                    $item = $validSizes[$validSizesKeys[$i]];
                    if (file_exists($item['path'])) {
                        return $this->getPreviewOrDownload($item['path'], $isDownload);
                    }
                }

                return $this->getVideo($mediaID, $mediaFileName, $mediaPath, $availableSizes, $sizeKey, 'raw', $thumbnail, $isDownload);
            }
            return $this->getPreviewOrDownload($validSizes[$sizeKey]['path'], $isDownload);
        }

        return $mediaPath;
    }

    private function getPreviewOrDownload($path, $isDownload)
    {
        if ($isDownload) {
            $video = new Video();
            $video->setPath($path);
            return $video;
        }
        return VideoStreamer::streamFile($path);
    }

    private function previewImage($mediaPath, $type = 'raw')
    {
        $manager = new ImageManager(['driver' => 'imagick']);
        $image   = $manager->make($mediaPath);
        $imageProcess = new MediaSizeImage($type, $mediaPath, $manager, $image);
        $extension = pathinfo($mediaPath, PATHINFO_EXTENSION);
        if ($type !== 'raw') {
            // if (!$imageProcess->checkSize()) $imageProcess->setSizeDefault();
            if (!$imageProcess->imageExists($extension)) {
                /*if (!$imageProcess->pngHasAlpha() && $extension === 'png') {
                    $extension = 'jpg';
                }*/
                $imageProcess->save($extension);
            }
        }
        $result = $imageProcess->getImage($extension);
        return $result;
    }

    public function saveVideoSnapshot($thumbPath, $videoSourcePath, $sec = 10)
    {
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
    public function addFromRequest(Model $model,  $collection, $customProperties, $files = null, $requestKey = null)
    {
        $collection = $collection ?? $this->defaultFileCollection;
        if (!empty($requestKey) && empty($files)) {
            $model->addMediaFromRequest($requestKey)->withCustomProperties($customProperties)->toMediaCollection($collection);
        }
        if (!empty($files) && empty($requestKey)) {
            $model->addMedia($files)->withCustomProperties($customProperties)->toMediaCollection($collection);
        }
        $model->save();

        $model = $model->fresh();

        $mediaList = $this->list($model, $collection);

        $media = Media::findOrFail($mediaList[0]->id);
        $mimeType = $media->mime_type;
        $mediaPath = $media->getPath();
        $fileType = explode('/', $mimeType)[0];
        if ($fileType == 'video') {
            $file_directory = str_replace($media->file_name, '', $mediaPath);
            $thumbnail = $file_directory . '/' . $media->filename . '__thumb_.png';
            $this->saveVideoSnapshot($thumbnail, $mediaPath);
        }
        if ($fileType === 'image') {
            //JAP se generan al vuelo
/*
            $manager = new ImageManager(['driver' => 'imagick']);
            $image    = $manager->make($mediaPath);
            $image2   = $manager->make($mediaPath);
            $thumb  = new MediaSizeImage('thumbnail', $mediaPath, $manager, $image);
            $small  = new MediaSizeImage('small', $mediaPath, $manager, $image2);
            $large  = new MediaSizeImage('large', $mediaPath, $manager, $image2);
            $extension = $image->extension;
            //if (!$large->pngHasAlpha()) $extension = 'jpg';
            if ($thumb->checkSize())   $thumb->save($extension);
            if ($small->checkSize())   $small->save($extension);
            $large->save($extension);
*/
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
    public function deleteOne(Media $media): bool
    {
        return $media->delete();
    }

    public function generateRenderKey()
    {
        $flag = false;
        $key = null;

        while (!$flag) {
            try {
                $key = DocumentRendererKey::generateKey();
                $keyEntry = DocumentRendererKey::create(['key' => $key]);
                $keyEntry->storeKeyExpirationDate();
                $flag = true;
            } catch (\Exception $e) {
                // echo $e->getMessage();
            }
        }

        return $key;
    }

    public function checkRendererKey($key, $method)
    {
        if ($method !== 'GET') return true;

        $flag = false;
        $keyEntry = DocumentRendererKey::where('key', $key)->first();

        if ($keyEntry === null) return false;

        $keyEntry->update();
        $keyEntry->increaseUsages();

        if ($keyEntry->downloadAllowed()) $flag = true;

        $this->removeStoredKeys();

        return $flag;
    }

    private function removeStoredKeys()
    {
        $existingKeys = DocumentRendererKey::get();
        foreach ($existingKeys as $key) {
            if ($key->mustBeRemoved()) {
                $key->delete();
            }
        }
    }

    public function getMediaURL(Model $model, $model_id)
    {
        $media = $model->where('model_id', $model_id)->first();
        return $media ? url('resource/render/' . DamUrlUtil::generateDamUrl($media,  $media->custom_properties['parent_id'])) : false;
    }
}
