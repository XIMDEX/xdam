<?php

namespace App\Services\ExternalApis;

use Exception;
use GuzzleHttp\Client;

class XevalService extends BaseApi
{
    public function __construct()
    {
        $this->BASE_URL = config('xeval.base_url');
        $this->VERSION = config('xeval.version');
    }

    public function getActivities($page = 0, $page_size = 20, $lang = null)
    {
        $query  = "activities?order_by=id&sort=desc&page=$page&limit=$page_size";
        if ($lang) {
            $lang = explode('-', $lang)[0];
            $lang = strtoupper($lang);
            $query .= "&language_default=$lang";
        }


        return $this->call("$this->BASE_URL/$this->VERSION/$query");
    }

    public function getAssessments($page = 0, $page_size = 20, $lang = null)
    {
        $query  = "assessments?order_by=id&sort=desc&lite=true&page=$page&limit=$page_size";
        if ($lang) {
            $lang = explode('-', $lang)[0];
            $lang = strtoupper($lang);
            $query .= "&language_default=$lang";
        }
        return $this->call("$this->BASE_URL/$this->VERSION/$query");
    }

    public function call(
        string $url,
        array $params = [],
        string $method = "get",
        string $body = "",
        bool $requiredAuth = false,
        array &$headers = [],
        bool $isKakuma = true,
        $authorization_token = false,
        $auth_type = false
    ) {

        $client = new Client();
        $res = $client->request(strtoupper($method), "$url");
        $body = $res->getBody()->getContents();

        $jsonBody = json_decode($body, true);
        return $jsonBody;
    }
}
