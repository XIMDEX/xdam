<?php

namespace App\Services\ExternalApis;

use stdClass;

class XowlTextService
{
    private array $request = [
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'multipart/form-data'
        ],
        'multipart' => [
            [
                'name' => 'interactive',
                'contents' => '1'
            ],
            [
                'name' => 'extra_links',
                'contents' => 'true'
            ],
            [
                'name' => 'options',
                'contents' => '{"watson": {"features": {"entities": {"mentions": false}}, "extra_links": true, "confidence": 1}, "dbpedia": {"confidence": 2, "extra_links": true}, "comprehend": {"LanguageCode": "es", "extra_links": true}, "confidence": 1, "extra_links": true}
                '
            ]
        ],
        'timeout' => 60
    ];
    private \GuzzleHttp\Client $client;
    private string $xowlUrl;
    public function __construct()
    {
        $this->client  = new \GuzzleHttp\Client();
        $this->xowlUrl = getenv('XOWL_URL');
    }
    //Mete file directamente
    public function getDataOwlFromFile(String $uuid, $file)
    {
        $result = new stdClass();
        $result->status = "FAIL";
        $promises[$uuid] = $this->setPromises($uuid);
        $responses = \GuzzleHttp\Promise\Utils::settle($promises)->wait();
        $response = array_shift($responses);
        if ($response['state'] === 'fulfilled') {
            $output_xowl = $response['value']->getBody()->getContents();
            $result = json_decode($output_xowl);
            $result->status = 'success';
        }
        return $result;
    }

    private function setFile(\Illuminate\Http\File $file)
    {
        $this->request['multipart'][] = [
            'name' => 'file',
            'contents' => fopen($file->getPathname(), 'r'),
            'filename' => $file->getFilename()
        ];
    }

    private function setPromises(String $uuid){
        $requestOwl = new \GuzzleHttp\Psr7\Request('POST', $this->xowlUrl . '/enhance/all?XDEBUG_SESSION_START=VSCODE');
        $requestOwl = $requestOwl->withBody(new \GuzzleHttp\Psr7\MultipartStream($this->request['multipart']));
        $promises[$uuid] = $this->client->sendAsync($requestOwl);
        return $promises;
    }
}
