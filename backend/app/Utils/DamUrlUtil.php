<?php

namespace App\Utils;

use App\Http\Resources\MediaResource;
use App\Models\Media;

class DamUrlUtil
{

    public static function decodeUrl($url)
    {
        $result = explode('@', $url);
        if (array_key_exists(6, $result))
        {
            return $result[6];
        }
        return false;
    }

    public static function generateDamUrl($media, $parent_id)
    {
        $file_type =  explode('/', $media->mime_type)[0];
        return "@@@dam:@$file_type@$parent_id@$media->id@@@";
    }
}
