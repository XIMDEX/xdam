<?php

namespace App\Services\Amazon;



class GetUrlResourceService
{
    public static function getResourceByCurl(string $url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $resourceContent = curl_exec($ch);
        if ($resourceContent === false) {
            curl_close($ch);
            throw new \Exception('Failed to retrieve resource from URL: ' . $url);
        }
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] != 200) {
            throw new \Exception('Resource not found or access denied for URL: ' . $url);
        }

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
