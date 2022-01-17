<?php

namespace App\Services;

use \GuzzleHttp\Client;
use App\Models\Collection;


class SemanticService
{

    private $client;
    private $xowlUrl;

    public function __construct() {

        $this->client = new Client();
        $this->xowlUrl = getenv('XOWL_URL');

    }

    public function enhance($semanticRequest) {

        $response = $this->client->post($this->getUrl($semanticRequest['enhancer']), [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'form_params' => $semanticRequest,
            'timeout' => 30
        ]);

        $enhancedText = $response->getBody()->getContents();
        
        return $this->createResourceStructure($enhancedText, $semanticRequest);

    }

    private function createResourceStructure($enhancedText, $semanticRequest) {

        return [
            'type' => 'document',
            'data' => [
                'description' => [
                    'active' => true,
                    // CHANGE NAME
                    'name' => 'test',
                    'tags' => $this->getTags(json_decode($enhancedText, true)),
                ],
                'semantic_data' => $enhancedText
            ],
            'collection_id' => Collection::where('name', 'Public Organization Document collection')->first()->id
        ];
    }

    private function getUrl($enhancer) {

        if ($enhancer == 'All') {
            return $this->xowlUrl . '/enhance/all';
        } else {
            return $this->xowlUrl . '/enhance';
        }

    }

    private function getTags($enhancedText) {

        $tags = array();

        foreach ($enhancedText['xtags'] as $entity) {
            if (!in_array($entity['text'], $tags)) {
                array_push($tags, $entity['text']);
            }
        }

        return $tags;
    }

}
