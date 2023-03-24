<?php


namespace App\Services\ExternalApis;

use Exception;

class KakumaService extends BaseApi
{

    public function __construct()
    {
        $this->BASE_URL = config('kakuma.base_url');
        $this->VERSION = config('kakuma.version');
    }

    public function loginAsSuperAdmin()
    {
        $loginData = $this->call(
            $this->BASE_URL . $this->VERSION . "/login",
            [
                "email" => config("kakuma.kakuma_admin_email"),
                "password" => config("kakuma.kakuma_admin_password"),
                "disable_redirect" => true
            ],
            "post"
        );

        return $loginData['access_token'];
    }


    public function getRecommendedCourses(string $userId)
    {
        if ($this->TOKEN == "") {
            $this->TOKEN = $this->loginAsSuperAdmin();
        }

        $headers['Authorization'] = 'Bearer ' . $this->TOKEN;
        
        return $this->call(
            $this->BASE_URL . $this->VERSION . "/recommendations/$userId", [], "get", "", true, $headers
        );
    }    

    public function softDeleteCourse(string $courseId, bool $force) 
    {
        if (!$this->TOKEN) {
            $this->TOKEN = $this->loginAsSuperAdmin();
        }

        $headers['Authorization'] = 'Bearer ' . $this->TOKEN;

        return $this->call(
            $this->BASE_URL . $this->VERSION . "/course/$courseId/soft?force=$force&only_local=true",
            [], "delete", "", true, $headers
        );
    }

    public function restoreCourse(string $courseId) 
    {
        if (!$this->TOKEN) {
            $this->TOKEN = $this->loginAsSuperAdmin();
        }

        $headers['Authorization'] = 'Bearer ' . $this->TOKEN;

        return $this->call(
            $this->BASE_URL . $this->VERSION . "/course/$courseId/restore?only_local=true",
            [], "get", "", true, $headers
        );
    }
}