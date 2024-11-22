<?php

namespace App\Services\Amazon;



class GetAmazonResourceService
{
    public static function getResourceByCurl(string $url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $resourceContent = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $tempFilePath = tempnam(sys_get_temp_dir(), 'remoteFile');
        file_put_contents($tempFilePath, $resourceContent);

        return new \Illuminate\Http\UploadedFile(
            $tempFilePath,
            basename(parse_url($url, PHP_URL_PATH)),
            $info['content_type'],
            $info['size_download'],
            UPLOAD_ERR_OK,
        );
    }
}
