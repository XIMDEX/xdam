<?php


namespace App\Services\Thumbnail;


use Alexisf\PreviewGenerator\PreviewGenerator;
use App\Models\File;
use App\Models\FilePreview;

class ThumbnailGeneratorService implements ThumbnailGeneratorInterface, ThumbnailGeneratorServiceInterface
{

    /**
     * Create a set of previews thumbnails for the current file
     */
    public function create( File $file )
    {
        $previewsPath = config('app.preview_files_dir', '../../../storage/previews');
        $tempFolder =  $previewsPath . DIRECTORY_SEPARATOR . 'temp';

        if (!file_exists($previewsPath)){
            mkdir( $previewsPath, 0755, true );
        }

        if (!file_exists($tempFolder)){
            mkdir( $tempFolder, 0755, true );
        }

        if (file_exists($file->dam_path)){
            try {
                // configuration array as template
                $options = array(
                    'tempFolder' => $tempFolder, // temporary folder
                    'exports' => [
                        'normal' => [
                            'width' => 800,		// width of the second image
                            'height' => 210,	// height of the second image
                            'mode' => 'preserveWidth',	// resize mode : contain = fill the image without exceeding, preserving the aspect ratio
                            'path' => $previewsPath,
                        ],
                    ],
                );

                $filePath = $file->dam_path;
                // complete the informations with path of destinations files
                $options['exports']['normal']['path'] .= DIRECTORY_SEPARATOR.$file->id.'.jpg';	// path of the second image to produce
                $obj = new PreviewGenerator($filePath, $options);	// create object
                $obj->processing();		// generate the images

                $preview = new FilePreview();
                $preview->local_path = $options['exports']['normal']['path'];
                $file->preview()->save($preview);
            } catch (Exception $e) {
                echo '',  $filePath, "\n";
                echo 'Exception : ',  $e->getMessage(), "\n";
            }
        }
    }
}
