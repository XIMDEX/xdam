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
                "disable_redirect" => 'EMPTY VALUE'
            ],
            "post"
        );

        return $loginData['access_token'];
    }
}
