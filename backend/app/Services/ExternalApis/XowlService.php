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

    public function getCaptionImage(string $url, string $lang = "")
    {
        //ambiguous name 
        $XWOLPetition = $this->BASE_URL . self::CAPTION . "?url=" . urlencode($url);

        if ($lang) {
            $XWOLPetition  .= "&lang=". $lang;
        }

        try {
            $response = $this->call( $XWOLPetition, [], "post" );
            $response = trim($response['caption'],'_');
        } catch (\Exception $exc) {
            $response = false;
        }

        return $response;
    }

}
