<?php

namespace App\Services\Media;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;

class MediaSizeImage
{
    private array $allowed_sizes =   ['thumbnail', 'small', 'medium', 'raw', 'default'];
    private array $sizes = [
        'thumbnail' => array('width' => 256, 'height' => 144),
        'small'     => array('width' => 426, 'height' => 240),
        'medium'    => array('width' => 854, 'height' => 480),
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
   public function __construct(string $size, string $path)
   {
    $this->size;
    $this->manager = new ImageManager(['driver' => 'imagick']);
    $this->image   = Image::make($path);
   }

   public function save($path,$type){
    $pathSave = $this->image->dirname."/__".$type.".jpg";
    $this->image->encode('jpg', $this->qualities[$type])->save($pathSave);
   }

   public function imageExists($type){
    $result = false;
    $path = $this->image->dirname;
    if ($type !== 'default') {
        $path = $this->image->dirname."/__".$type.".jpg";
    }
    $result = file_exists($path);
    return $path;
   }

   public function getImage(){
    return  $this->manager->make($this->image->dirname."/__".$type.".jpg");
   }

}
