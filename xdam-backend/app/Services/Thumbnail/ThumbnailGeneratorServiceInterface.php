<?php

namespace App\Services\Thumbnail;

use App\Models\File;

interface ThumbnailGeneratorServiceInterface
{
    /**
     * Create a set of previews thumbnails for the current file
     * @param File $file
     */
    public function create( File $file );
}
