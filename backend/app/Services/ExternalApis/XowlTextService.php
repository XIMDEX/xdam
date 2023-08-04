<?php

namespace App\Services\ExternalApis;


class XowlTextService
{
    private  $defaultOptions = [
        "watson" => ["features" => ["entities" => ["mentions" => false]], "extra_links" => true, "confidence" => 1],
        "dbpedia" => ["confidence" => 2, "extra_links" => true],
        "comprehend" => ["LanguageCode" => "es", "extra_links" => true], "confidence" => 1,
        "extra_links" => true
    ];
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

    public function getDataOwlFromFile($data,$params = [])
    {
        $result = [];

        //fin seteo optios
        $params['interactive'] = 1;
        $params['extra_links'] = true;
        $this->request['multipart'][] = [
            
                'name' => 'options',
                'contents' => json_encode($this->defaultOptions)
            
        ];

        if (isset($params['File'])) {
            $file = new \Illuminate\Http\File($params['File'][0]->getRealPath());
            $this->request['multipart'][] = [
                'name' => 'file',
                'contents' => fopen($file->getPathname(), 'r'),
                'filename' => $params['File'][0]->getClientOriginalName()
            ];
        }
        $requestOwl = new \GuzzleHttp\Psr7\Request('POST', $this->xowlUrl . '/enhance/all?XDEBUG_SESSION_START=VSCODE');
        $requestOwl = $requestOwl->withBody(new \GuzzleHttp\Psr7\MultipartStream($this->request['multipart']));

        $promises[$data->uuid] = $this->client->sendAsync($requestOwl);

        $responses = \GuzzleHttp\Promise\Utils::settle($promises)->wait();

        $response = array_shift($responses);
        if ($response['state'] === 'rejected') {
            $result = [
                'id' => $data->id,
                'uuid' => $data->uuid,
                'title' => $data->title,
                'status' => 'FAIL'
            ];
        } else {
            $output_xowl = $response['value']->getBody()->getContents();
            $result = json_decode($output_xowl);
        }
        return $result;
    }

    /**
     * Delete duplicate xtag.
     *
     * @param array $xtags The array of xtags.
     *
     * @return array The array of xtags without duplicates.
     */
    private function deleteDuplicateXtag($xtags)
    {
        $result = [];
        $aux    = [];
        foreach ($xtags as $xtag) {
            if (!in_array($xtag->text, $aux)) {
                $result[] = $xtag;
                $aux[] = $xtag->text;
            }
        }
        return $result;
    }
    /**
     * Check non-linked.
     *
     * @param array $linked The linked array.
     * @param array $nonLinked The non-linked array.
     *
     * @return array The result array.
     */
    private function checkNonLinked(array $linked, array $nonLinked)
    {
        $result = [];
        $linkedTexts = array_map(function ($link) {
            return $link->text;
        }, $linked);
        foreach ($nonLinked as $tag) {
            if (!in_array($tag->text, $linkedTexts)) $result[] =  $tag;
        }
        return $result;
    }

    public function getInfoXtags($entity, $withURL)
    {
        $output = [
            'name' => $entity->text,
            'confidence' => $entity->confidence,
            'type' => $entity->type,
            'start' => $entity->start,
            'end' => $entity->end,
        ];

        if ($withURL) $output['uri'] = isset($entity->dbpedia_uri) ? $entity->dbpedia_uri : $entity->uri;

        return $output;
    }
}
