<?php


namespace App\Services\ExternalApis;

use Exception;

class XTagsService extends BaseApi
{
    public function __construct()
    {
        $this->BASE_URL = config('xtags.base_url');
        $this->VERSION = config('xtags.version');
    }

    public function getXTags($id, $type, $lang)
    {
        return $this->call("$this->BASE_URL/$this->VERSION/resource-tags/$id?type=$type&lang=$lang");
    }

    public function setXTags($id, $type, $lang, $data)
    {
        $response = $this->call(
            "$this->BASE_URL/$this->VERSION/resource-tags/$id?type=$type&lang=$lang",
            $data,
            "post"
        );
        return $response;
    }
}
