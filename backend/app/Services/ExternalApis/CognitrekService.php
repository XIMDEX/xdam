<?php 
namespace App\Services\ExternalApis;

use GuzzleHttp\Client;

class CognitrekService extends BaseApi
{
    
    public function __construct(){
        $this->BASE_URL = config('cognitrek.base_url');
        $this->VERSION = config('cognitrek.version');
    }

    public function store($resourceID){
        $client = new Client();
        $res = $client->request(strtoupper("POST"),("{$this->BASE_URL}/resource/".$resourceID),['json' => ["resource_id" => $resourceID]]);
        return $res;
    }
}