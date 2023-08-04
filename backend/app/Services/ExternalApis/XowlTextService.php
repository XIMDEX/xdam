<?php

use GuzzleHttp\Psr7\Request;

class XowlTextService
{
    private array $defaultOptions = [
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
        'multipart' => [],
        'timeout' => 60
    ];
    private \GuzzleHttp\Client $client;
    private string $xowlUrl;
    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
        $this->xowlUrl = getenv('XOWL_URL');
    }

    public function getDataOwlFromFile($data, $enhance, $params = [])
    {
        $finalResult = [];

        //fin seteo optios
        $params['interactive'] = 1;
        $params['extra_links'] = true;


        if (isset($params['File'])) {
            $file = new \Illuminate\Http\File($params['File'][0]->getRealPath());
            $options['multipart'][] = [
                'name' => 'file',
                'contents' => fopen($file->getPathname(), 'r'),
                'filename' => $params['File'][0]->getClientOriginalName()
            ];
        }
        $options['multipart'][] = [
            'name' => 'options',
            'contents' => json_encode($this->defaultOptions)
        ];
        // Add interactive field
        $options['multipart'][] = [
            'name' => 'interactive',
            'contents' => '1'
        ];
        $request = new GuzzleHttp\Psr7\Request('POST', $this->xowlUrl . '/enhance/all?XDEBUG_SESSION_START=VSCODE');
        $request = $request->withBody(new GuzzleHttp\Psr7\MultipartStream($options['multipart']));

        $promises[$data->uuid] = $this->client->sendAsync($request);

        $responses = GuzzleHttp\Promise\Utils::settle($promises)->wait();

        $response = array_shift($responses);
        if ($response['state'] === 'rejected') {
            $finalResult = [
                'id' => $data->id,
                'uuid' => $data->uuid,
                'title' => $data->title,
                'status' => 'FAIL'
            ];
        } else {
            $output_xowl = $response['value']->getBody()->getContents();
            $result = json_decode($output_xowl);
            $xtags = $this->deleteDuplicateXtag($result->data->xtags);
            $xtags_interlinked = $this->deleteDuplicateXtag($result->data->xtags_interlinked);
            $xtags = $this->checkNonLinked($xtags_interlinked, $xtags);
            foreach ($xtags as &$tag) {
                $tag = $this->getInfoXtags($tag, false);
            }
            foreach ($xtags_interlinked  as &$tag) {
                $tag = $this->getInfoXtags($tag, true);
            }
            $finalResult['xtags'] = $xtags;
            $finalResult['xtags_interlinked'] = $xtags_interlinked;
        }


        return $finalResult;
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
