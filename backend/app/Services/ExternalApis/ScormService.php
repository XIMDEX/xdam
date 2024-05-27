<?php


namespace App\Services\ExternalApis;

use Exception;

class ScormService extends BaseApi
{

    public function __construct()
    {
        $this->BASE_URL = config('scorm.base_url');
        $this->VERSION = config('scorm.version');
        $this->TOKEN = config('scorm.token');
    }

    public function cloneBook($id) {
        try {
            $response = $this->call($this->BASE_URL . $this->VERSION . "/api/book/". $id ."/clone", [], "post", json_encode(['token' => $this->TOKEN]));
            return $response;
        } catch (\Exception $exc) {
            throw new \Exception('Error book processing');
        }
    }
}