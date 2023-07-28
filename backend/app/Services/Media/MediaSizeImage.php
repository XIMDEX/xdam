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
    private float $height;
    private float $width;
   public function __construct(string $size, string $path,ImageManager $manager,Image $image)
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
   public function save(){
    $pathSave = $this->image->dirname."/__".$this->size.".jpg";
    $aspectRatio = $this->getAspectRatio($this->width /  $this->height);
    if ($this->size === 'default'){
            $pathSave = $this->path;
            $this->image->save($pathSave);
    }else{
            $this->image->resize($aspectRatio['width'],$aspectRatio['height'])->save($pathSave);
    }
   }
   /**
    * Check the image's size
    *
    * @return boolean
    */
   public function checkSize(){
    $result = true;
    $widthNew       = $this->sizes[$this->size]['width'];
    $heightNew      = $this->sizes[$this->size]['height'] ;
    if ($widthNew >=  $this->width &&  $heightNew >=  $this->height ) $result = false;
    return $result;
   }    

   /**
    * Check if a specific image exits.
    *
    * @return boolean
    */
   public function imageExists(){
    $result = false;
    $path = $this->path;
    if ($this->size !== 'default') {
        $path = $this->image->dirname."/__".$this->size.".jpg";
    }
    $result = file_exists($path);
    return $result;
   }

   /**
    * Return an image
    *
    * @return \Intervention\Image\Image
    */
   public function getImage(){
    $result = $this->image->dirname."/__".$this->size.".jpg";
    if($this->size === "default" )$result = $this->path;
    return  $this->manager->make($result);
   }

   private function getAspectRatio($aspectRatio){
    if ($aspectRatio >= 1.0 && $this->height<=$this->sizes[$this->size]['height']) { // Horizontal
        $newWidth = $this->sizes[$this->size]['width'];
        $newHeight = $newWidth / $aspectRatio;
    } else { // Vertical
        $newHeight = $this->sizes[$this->size]['height'];
        $newWidth = $newHeight * $aspectRatio;
    }
    $result = ["height" => $newHeight,"width" => $newWidth];
    return $result;
   }

   public function setSizeDefault(){
    $this->size = 'default';
   }

}
