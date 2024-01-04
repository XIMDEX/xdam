<?php
namespace App\Services\ExternalApis;

use GuzzleHttp\Client;
use stdClass;

class XevalSyncActivityService extends BaseApi{
    const ACTIVITY = 'activity';
    const BASE_URL = "";
    const VERSION  = "";

    public function __construct()
    {
        $this->BASE_URL = config('xeval.base_url');
        $this->VERSION = config('xeval.version');
    }

    public function syncActivityOnXeval($data){
        $client = new Client();
        $xeval_id = $data["xeval_id"];
        $res = $client->request(strtoupper("PUT"),("{$this->BASE_URL}/api/v1.0/activities/{$xeval_id}"),['json' => $data]);
        return $res;
    }

    public function parseActivityData($id,$activity, $collection_id)
    {
        $description = $activity->description; 
        $data = [
            'external_id' => $id,
            'collection_id' => $collection_id,
            ...get_object_vars($description),
            'units' => $description->unit ?? [],
            'from' => "xdam"
        ];
        $feedbacks = $data["feedbacks"];
        $feedbacks = str_replace("'", "\"", $feedbacks);
        $data['feedbacks'] = json_decode($feedbacks, true);
        $assessments = [];
        //$_assessments = array_column($description->assessments, 'id');
        /*foreach ($_assessments as $assessment_id) {
            $assessments[] = intval($assessment_id);
        }
        $data['assessments'] = $assessments;*/
        return $data;
    }
}