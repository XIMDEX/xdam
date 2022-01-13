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
        
        return $this->createResourceStructure($enhancedText);

    }

    private function createResourceStructure($enhancedText) {

        return [
            'type' => 'document',
            'data' => [
                'description' => [
                    'active' => true,
                    'name' => 'test',
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

}
