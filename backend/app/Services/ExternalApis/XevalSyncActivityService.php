<?php
namespace App\Services\ExternalApis;

use GuzzleHttp\Client;
use stdClass;

class XevalSyncActivityService extends BaseApi{
    const ACTIVITY = 'activity';
    const BASE_URL = "";
    const VERSION  = "";
    //update xeval: /activities/<int:id>
    public function __construct()
    {
        $this->BASE_URL = config('xeval.base_url');
        $this->VERSION = config('xeval.version');
    }

    public function syncActivity($id,$data){
        $client = new Client();
        $res = $client->request(strtoupper("PUT"),("{$this->BASE_URL}/activities/$id"),[
            $data
        ]);
        return $res;
    }

    public function parseActivityData($id,$activity, $collection_id)
    {
        $description = $activity->description; 
        $data = [
            'external_id' => $id,
            'collection_id' => $collection_id,
            'type' => self::ACTIVITY,
            'data' => new stdClass(),
            'status' => $description->active ?? false
        ];
        $assessments = [];
        $_assessments = array_column($description->assessments, 'id');
        foreach ($_assessments as $assessment_id) {
            $assessments[] = intval($assessment_id);
        }
        $data['data']->description = new stdClass();
        $data['data']->description->xeval_id = $id;
        $data['data']->description->name = $description->name ?? "Un-named ID {$id}";
        $data['data']->description->description = $description->description;
        $data['data']->description->type = $description->type;
        $data['data']->description->language_default = $description->language_default;
        $data['data']->description->available_languages = $description->available_languages;
        $data['data']->description->isbn = $description->isbn ?? [];
        $data['data']->description->unit = $description->units ?? [];
        $data['data']->description->active = $description->active;
        $data['data']->description->assessments = $assessments;
        if (isset($description->tags)) {
            $data['data']->description->tags = $description->tags;
        }
        return $data;
    }
}