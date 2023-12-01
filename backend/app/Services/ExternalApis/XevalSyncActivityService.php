<?php
namespace App\Services\ExternalApis;

use GuzzleHttp\Client;
use stdClass;

class XevalSyncService extends BaseApi{
    const ACTIVITY = 'activity';
    const BASE_URL = "";
    const VERSION  = "";
    //update xeval: /activities/<int:id>
    public function __construct()
    {
        $this->BASE_URL = config('xeval.base_url');
        $this->VERSION = config('xeval.version');
    }

    public function sync(int $id,$data){
        $client = new Client();
        $res = $client->request(strtoupper("PUT"),("{$this->BASE_URL}/activities/$id?XDEBUG_SESSION_START=VSCODE"),[
            $data
        ]);
    }

    public function parseActivityData($activity, $collection_id)
    {
        $data = [
            'external_id' => $activity['id'],
            'collection_id' => $collection_id,
            'type' => self::ACTIVITY,
            'data' => new stdClass(),
            'status' => $activity['status'] === 'ACTIVE'
        ];
        $assessments = [];
        $_assessments = array_column($activity['assessments'], 'id');
        foreach ($_assessments as $assessment_id) {
            $assessments[] = intval($assessment_id);
        }
        $data['data']->description = new stdClass();
        $data['data']->description->xeval_id = $activity['id'];
        $data['data']->description->name = $activity['name'] ?? "Un-named ID {$activity['id']}";
        $data['data']->description->description = $activity['title'];
        $data['data']->description->type = $activity['type'];
        $data['data']->description->language_default = $activity['language_default'];
        $data['data']->description->available_languages = $activity['available_languages'];
        $data['data']->description->isbn = $activity['isbn'] ?? [];
        $data['data']->description->unit = $activity['units'] ?? [];
        $data['data']->description->active = $activity['status'] === 'ACTIVE';
        $data['data']->description->assessments = $assessments;
        if ($activity['tags']) {
            $data['data']->description->tags = $activity['tags'];
        }
        return $data;
    }
}