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

    function copyRemote($fromUrl, $toFile)
    {
        try {
            $client = new Client();
            $headers = ['sink' => $toFile];
            if ($this->TOKEN) {
                $headers['headers']["Authorization"] = "$this->AUTH_TYPE $this->TOKEN";
            }
            $data = $client->request('GET', $fromUrl, $headers)->getHeaders();
            $contentDisposition = $data["Content-Disposition"][0];
            $exploded = explode(".", $contentDisposition);
            $extension = end($exploded);
            $extension = rtrim($extension, '"');

            File::move($toFile, "$toFile.$extension");
            return basename($toFile) . "." . $extension;
        } catch (Exception | GuzzleException $e) {
            throw new Exception($e);
            return false;
        }
    }

    public function call(
        string $url,
        array $params = [],
        string $method = "get",
        string $body = "",
        bool $requiredAuth = false,
        array &$headers = []
    ) {
        try {
            if ($requiredAuth && !array_key_exists("Authorization", $headers)) {
                $kakuma_token = Auth::user()->kakuma_token ?? config('kakuma.token');
                $headers['Authorization'] = "$this->AUTH_TYPE $kakuma_token";
            }

            if ($body) {
                return Http::withHeaders(
                    $headers
                )->withBody($body, 'application/json')->$method(
                    $url,
                    $params
                )->throw()->json();
            }

            return Http::withHeaders(
                $headers
            )->$method(
                $url,
                $params
            )->throw()->json();
        } catch (\Throwable $th) {
            throw new Exception($th);
        }
    }
}
