<?php

namespace App\Services\ExternalApis\Xowl;

use App\Services\ExternalApis\BaseApi;
use Illuminate\Support\Facades\Storage;

class XowlImageService extends BaseApi
{

    protected string $BASE_URL;
    private string $lang;

    public function __construct(){
        $this->BASE_URL = config('ximdex.XOWL_URL');
        $this->lang = 'ES'; 
    }


    public function getCaptionFromImage($mediaUrl)
    {
       
        try {
            $XWOLPetition = $this->BASE_URL . "/caption". "?url=" . urlencode($mediaUrl);

         /*   if ($this->lang) {
                $XWOLPetition  .= "&lang=" . $this->lang;
            }*/
            $XWOLPetition .= "&XDEBUG_SESSION_START=VSCODE";
            try {
                $response = $this->call($XWOLPetition, [], "post");
                $response = trim($response['caption'], '_');
            } catch (\Exception $exc) {
                $response = $exc;
            }
            return $response;

        } catch (\Exception $exc) {
            // failed captioning image -- continue process
        }
    }

  /*  public function saveCaptionImage(string $caption, string $uuid, string $uuidParent)
    {
        $result = ["imageCaptionAi" => $caption];
        if (!Storage::disk('semantic')->exists($uuidParent."/".$uuid . "json")) {
            Storage::disk('semantic')->put($uuidParent."/".$uuid . ".json", json_encode($result));
        }
 
    }*/



    /*const CAPTION = '/caption';
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
    }*/
}
