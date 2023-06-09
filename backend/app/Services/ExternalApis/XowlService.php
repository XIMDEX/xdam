<?php

namespace App\Services\ExternalApis;

class XowlService extends BaseApi
{
    const CAPTION = '/caption';
    const TRANSLATE = '/translate';

    public function __construct()
    {
        $this->BASE_URL = config('ximdex.XOWL_URL');
    }

    public function getCaption(string $url, $lang = false)
    {
        $url = $this->BASE_URL . self::CAPTION . "?url=" . urlencode($url);

        if ($lang) {
            $url .= "&lang=". $lang;
        }

        try {
            $response = $this->call( $url, [], "post" );
            $response = $response['caption'];
        } catch (\Exception $exc) {
            $response = false;
        }

        return $response;
    }

}
