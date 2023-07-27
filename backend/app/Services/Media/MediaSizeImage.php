<?php

namespace App\Services\Media;
use Intervention\Image\Image;
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
   public function __construct(string $size, string $path,ImageManager $manager,Image $image)
   {
    $this->size = $size;
    $this->manager = $manager;
    $this->image   = $image;
   }

   public function save(){
    $pathSave = $this->image->dirname."/__".$this->size.".jpg";
    $this->image->resize($this->sizes[$this->size]['width'],$this->sizes[$this->size]['height'])->save($pathSave);
   }

   public function imageExists(){
    $result = false;
    $path = $this->image->dirname;
    if ($this->size !== 'default') {
        $path = $this->image->dirname."/__".$this->size.".jpg";
    }
    $result = file_exists($path);
    return $result;
   }

   public function getImage(){
    return  $this->manager->make($this->image->dirname."/__".$this->size.".jpg");
   }

}
