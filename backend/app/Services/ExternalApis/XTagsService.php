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

    public function saveXTagsResource($id, $context, $lang, $data)
    {
        $bodies = [];
        foreach ($data as $tag) {
            if (!isset($bodies[$tag->vocabulary])) {
                $bodies[$tag->vocabulary] = [
                    'resourceId' => $id,
                    'tags' => [],
                    'vocabulary' => $tag->vocabulary,
                ];
            }
            $bodies[$tag->vocabulary]['tags'][] = [
                'context' => $context,
                'definitionId' => $tag->id,
                'langId' => 1,
                'lang' => $lang,
                'name' => $tag->label ?? $tag->name,
                'typeId' => 1,
            ];
        }
        $response = [];
        foreach ($bodies as $body) {

            $response[] = $this->call(
                "$this->BASE_URL/$this->VERSION/resource-tags/$id",
                $body,
                "post"
            );
        }
        return $response;
    }
}
