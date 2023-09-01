<?php

namespace App\Services\ExternalApis\Xowl;

use App\Services\ExternalApis\BaseApi;

class XowlImageService extends BaseApi
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
            $XWOLPetition  .= "&lang=" . $lang;
        }

        try {
            $response = $this->call($XWOLPetition, [], "post");
            $response = trim($response['caption'], '_');
        } catch (\Exception $exc) {
            $response = false;
        }

        return $response;
    }

    private function getCaptionFromImage(string $mediaUrl)
    {
        try {
            return $caption = $this->xowlImageService->getCaptionImage($mediaUrl, env('BOOK_DEFAULT_LANGUAGE', 'en')) ?? "";
        } catch (\Exception $exc) {
            // failed captioning image -- continue process
        }
    }

    private function saveCaptionImage(string $caption, string $uuid)
    {
        $result = ["imageCaptionAi" => $caption];
        if (Storage::disk('semantic')->exists($uuid . "json")) {
            $file = json_decode(Storage::disk("semantic")->get($uuid . ".json"));
            $file->imageCaptionAi = $caption;
            $result = json_encode($file);
        }
        Storage::disk('semantic')->put($uuid . ".json", json_encode($result));
    }
}
