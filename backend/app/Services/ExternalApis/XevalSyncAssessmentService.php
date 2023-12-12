<?php 
namespace App\Services\ExternalApis;

use GuzzleHttp\Client;

class XevalSyncAssessmentService extends BaseApi
{
    const ASSESSMENT = 'assessment';
    const BASE_URL = "";
    const VERSION  = "";
    
    public function __construct(){
        $this->BASE_URL = config('xeval.base_url');
        $this->VERSION = config('xeval.version');
    }

    public function syncAssessmentOnXeval($data){
        $client = new Client();
        $xeval_id = $data["xeval_id"];
        $res = $client->request(strtoupper("PUT"),("{$this->BASE_URL}/api/v1.0/assessments/{$xeval_id}"),['json' => $data]);
        return $res;
    }

    public function parseAssessmentData($id,$assessment, $collection_id)
    {   
        $description = $assessment->description;
        $data = [
            'external_id' => $id,
            'collection_id' => $collection_id,
            'type' => self::ASSESSMENT,
            'title' => $description->name,
            ...get_object_vars($description),
        ];
        $activities = [];
        $_activities = array_column($description->activities, 'id');
        foreach ($_activities as $activity_id) {
            $activities[] = intval($activity_id);
        }
        return $data;
    }

}