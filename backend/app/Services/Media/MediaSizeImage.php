<?php

namespace App\Services\Media;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class MediaSizeImage
{
    private array $allowed_sizes =   ['thumbnail', 'small', 'medium', 'raw', 'large', 'largeHD', 'default'];
    private array $sizes = [
        'thumbnail' => array('width' => 256, 'height' => 144),
        'small'     => array('width' => 426, 'height' => 240),
        'medium'    => array('width' => 854, 'height' => 480),
        'largeHD'     => array('width' => 1920, 'height' => 1080),
        'large'     => array('width' => 3840, 'height' => 2160),
        'raw'       => 'raw',
        'default'   => array('width' => 1280, 'height' => 720)
    ];
    private array $qualities = [
        'thumbnail' => 25,
        'small'     => 25,
        'medium'    => 50,
        'raw'       => 'raw',
        'default'   => 90
    ];
    private string $size;
    private ImageManager $manager;
    private Image $image;
    private string $path;
    private float $height;
    private float $width;
    public function __construct(string $size, string $path, ImageManager $manager, Image $image)
    {
        $this->size = $size;
        $this->manager = $manager;
        $this->image   = $image;
        $this->path = $path;
        $this->height =  $this->image->height();
        $this->width  =  $this->image->width();
    }
    /**
     * Save the image.
     *
     * @return void
     */
    public function save(String $extension)
    {
        $pathSave = $this->image->dirname . "/__" . $this->size . ".$extension";

        if ($this->size === 'default') {
            $pathSave = $this->path;
            $this->image->save($pathSave);
        } else {
            $aspectRatio = $this->getAspectRatio();
            $this->image->resize($aspectRatio['width'], $aspectRatio['height'])->save($pathSave);
        }
    }
    /**
     * Check the image's size
     *
     * @return boolean
     */
    public function checkSize()
    {
        $result = true;
        $widthNew       = $this->sizes[$this->size]['width'];
        $heightNew      = $this->sizes[$this->size]['height'];
        if ($widthNew >=  $this->width &&  $heightNew >=  $this->height) $result = false;
        return $result;
    }

    /**
     * Check if a specific image exits.
     *
     * @return boolean
     */
    public function imageExists(String $extension)
    {
        $result = false;
        $path = $this->path;
        if ($this->size !== 'default') {
            $path = $this->image->dirname . "/__" . $this->size . ".$extension";
        }
        $result = file_exists($path);
        return $result;
    }

    /**
     * Return an image
     *
     * @return \Intervention\Image\Image
     */
    public function getImage(String $extension)
    {
        $result = $this->image->dirname . "/__" . $this->size . ".$extension";
        if ($this->size === "default" || $this->size === "raw") return $this->image;  //$result = $this->path;
        return  $this->manager->make($result);
    }

    private function getAspectRatio()
    {
        $originalWidth = $this->width;
        $originalHeight = $this->height;


        $targetWidth = $this->sizes[$this->size ]['width'];
        $targetHeight = $this->sizes[$this->size ]['height'];


        $isVertical = $originalHeight > $originalWidth;

        if ($originalWidth <= $targetWidth && $originalHeight <= $targetHeight) {
            return ["height" => $originalHeight, "width" => $originalWidth];
        }

        if ($isVertical) {
            $newHeight = $targetWidth;
            $newWidth = round($newHeight*$originalWidth / $originalHeight);
        } else {
            $ratio = $originalWidth / $targetWidth;
            $newWidth = $targetWidth;
            $newHeight = round($originalHeight / $ratio);
        }

        return ["height" => $newHeight, "width" => $newWidth];
    }

    public function setSizeDefault()
    {
        $this->size = 'default';
    }

    public function pngHasAlpha()
    {
        return strpos($this->image->encode('png')->getEncoded(), 'tRNS') !== false;
    }
}
