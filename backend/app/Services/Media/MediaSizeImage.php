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
    private string $path;
   public function __construct(string $size, string $path,ImageManager $manager,Image $image)
   {
    $this->size = $size;
    $this->manager = $manager;
    $this->image   = $image;
    $this->path = $path;
   }

   public function save(){
    $pathSave = $this->image->dirname."/__".$this->size.".jpg";
    $aspectRatio = $this->getAspectRatio($this->sizes[$this->size]['width'] / $this->sizes[$this->size]['height']);
    if ($this->size === 'default'){
        $pathSave = $this->path;
        $this->image->save($pathSave);
    }else{
        $this->image->resize($this->sizes[$this->size]['width'],$aspectRatio)->save($pathSave);
    }

   }

   public function imageExists(){
    $result = false;
    $path = $this->path;
    if ($this->size !== 'default') {
        $path = $this->image->dirname."/__".$this->size.".jpg";
    }
    $result = file_exists($path);
    return $result;
   }

   public function getImage(){
    $result = $this->image->dirname."/__".$this->size.".jpg";
    if($this->size === "default")$result = $this->path;
    return  $this->manager->make($result);
   }

   private function getAspectRatio($aspectRatio){
    if ($aspectRatio >= 1.0) { // Horizontal
        $newWidth = $this->sizes[$this->size]['width'];
        $newHeight = $newWidth / $aspectRatio;
    } else { // Vertical
        $newHeight = $$this->sizes[$this->size]['height'];
        $newWidth = $newHeight * $aspectRatio;
    }
    $result = ["height" => $newHeight,"width" => $newWidth];
   }

}
