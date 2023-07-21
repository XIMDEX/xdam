<?php


namespace App\Services\ExternalApis;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class BaseApi
{
    protected string $BASE_URL = "";
    protected string $VERSION = "";
    protected string $TOKEN = "";
    protected string $AUTH_TYPE = "Bearer";

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
        $authType = $auth_type ? $auth_type : $this->AUTH_TYPE;
        try {
            if ($isKakuma && $requiredAuth && !array_key_exists("Authorization", $headers)) {
                $kakuma_token = Auth::user()->kakuma_token ?? config('kakuma.token');
                $headers['Authorization'] = "$authType $kakuma_token";
            }

            if ($authorization_token) {
                $headers['Authorization'] = "$authType $authorization_token";
            }

            if ($body) {
                return Http::withHeaders($headers)
                    ->withBody($body, 'application/json')
                    ->$method($url, $params)
                    ->throw()
                    ->json();
            }

            return Http::withHeaders($headers)
                ->$method($url, $params)
                ->throw()
                ->json();
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
    }
}
