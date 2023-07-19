<?php

namespace App\Services;

use \GuzzleHttp\Client;
use App\Models\Collection;
use App\Models\DamResource;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class SemanticService
{
    const PAGE = 0;
    const PAGE_SIZE = 5;

    private $client;
    private $xowlUrl;

    public function __construct()
    {

        $this->client = new Client();
        $this->xowlUrl = getenv('XOWL_URL');
    }

    public function enhance($semanticRequest)
    {
        $uuid = Str::orderedUuid()->toString();

        if (
            isset($semanticRequest['options']) &&
            isset($semanticRequest['options']['comprehend']) &&
            isset($semanticRequest['options']['comprehend']['LanguageCode'])
        ) {
            $langcode = $semanticRequest['options']['comprehend']['LanguageCode'];
        } else {
            $langcode = 'es';
        }

        $errors = [];
        $resourceStructure = [];

        $resources = [
            $uuid => [
                'id' => $uuid,
                'uuid' => $uuid,
                'title' => isset($semanticRequest['title']) ? $semanticRequest['title'] : $uuid,
                'body' => $this->cleanText($semanticRequest['text']),
                'language' => $langcode,
                'category' => isset($semanticRequest['category']) ? $semanticRequest['category'] : 'Otros',
                'external_url' => isset($semanticRequest['external_url']) ? $semanticRequest['external_url'] : '',
                'image' => isset($semanticRequest['image']) ? $semanticRequest['image'] : ''
            ]
        ];

        $this->concurrentPost($resources, $errors, $semanticRequest['enhancer'], $semanticRequest);

        foreach ($resources as $resource) {
            $resourceStructure[] = $this->createResourceStructure($resource, $semanticRequest);
        }

        return [
            'resources' => $resourceStructure,
            'errors' => $errors
        ];
    }


    public function
    enhanceV2($semanticRequest)
    {
        $uuid = Str::orderedUuid()->toString();
        $data = json_decode($semanticRequest['data']);
        $dataFilter = $data->description;
        if (
            $dataFilter->language
        ) {
            $langcode =  $dataFilter->language;
        } else {
            $langcode = 'es';
        }

        $errors = [];
        $resourceStructure = [];
        $dataFilter->id = $uuid;
        $dataFilter->uuid = $uuid;
        $dataFilter->title = isset($dataFilter->title) ? $dataFilter->title : $uuid;
        $dataFilter->body = $this->cleanText($dataFilter->body);
        $dataFilter->language = $langcode;
        $dataFilter->category = isset($dataFilter->category) ? $dataFilter->category : 'Otros';
        $dataFilter->external_url = isset($dataFilter->external_url) ? $dataFilter->external_url : '';
        $dataFilter->image = isset($dataFilter->image) ? $dataFilter->image : '';

        $dataResult = $this->getDataOwl($dataFilter, $errors, $dataFilter->enhanced, $semanticRequest);
        $path = Storage::disk('semantic')->put($dataFilter->uuid."/", json_encode($dataResult));
      
        $resourceStructure[] = $this->createResourceStructure2($dataFilter, $semanticRequest);
        return [
            'resources' => $resourceStructure,
            'errors' => $errors
        ];
    }

    public function automaticEnhance($dataFilter)
    {

        $countDocuments = DamResource::where('type', 'document')->get();
        if ($countDocuments) $countDocuments = count($countDocuments);

        $isEnhanceResource = !key_exists('only-text', $semanticRequest);

        // $page = key_exists('p', $semanticRequest) ? ($semanticRequest['p'] * self::PAGE_SIZE) + 1 : self::PAGE;
        $page = $countDocuments;
        $page_size = key_exists('ps', $semanticRequest) ? $semanticRequest['ps'] : self::PAGE_SIZE;
        $enhance = key_exists('enhancer', $semanticRequest) ? $semanticRequest['enhancer'] : 'All';
        $interactive = 1; // key_exists('interactive', $semanticRequest) && $semanticRequest['interactive'] == 1 ? 1 : 0;
        $type = key_exists('type', $semanticRequest) ? $semanticRequest['type'] : 'all';

        $errors = [];
        $resourcesInesJA = $this->getDataINES($page, $page_size, $type);
        $isEnhanceResource ? $this->concurrentPost($resourcesInesJA, $errors, $enhance) : $this->addOnlyResource($resourcesInesJA);

        $resources = [];
        foreach ($resourcesInesJA as $resource) {
            $resources[] = $this->createResourceStructure($resource, $semanticRequest);
        }

        return [
            'resources' => $resources,
            'errors' => $errors
        ];
    }

    public function updateWithEnhance($semanticResource, $semanticRequest)
    {

        $resourceToEnhance = [];
        $errors = [];

        $resourceToEnhance[$semanticResource->uuid] = json_decode(json_encode($semanticResource), true);

        $this->concurrentPost($resourceToEnhance, $errors, $semanticRequest['enhancer'], $semanticRequest);

        $enhancedResource = $this->createResourceStructure($resourceToEnhance[$semanticResource->uuid], $semanticRequest);

        return [
            'resources' => $enhancedResource,
            'errors' => $errors
        ];
    }

    private function createResourceStructure($resource, $params)
    {
        $entities_linked = [];
        $entities_non_linked = [];
        $array_linked = [];

        if (key_exists('xtags_interlinked', $resource)) {
            foreach ($resource['xtags_interlinked'] as $key => $entity) {
                $entities_linked[] = $this->getInfoXtags($entity, true);
                $array_linked[] = $key;
                unset($resource['xtags_interlinked']);
            }
        }

        if (key_exists('xtags', $resource)) {
            foreach ($resource['xtags'] as $key => $entity) {
                if (!in_array($key, $array_linked)) {
                    $entities_non_linked[] = $this->getInfoXtags($entity, false);
                }
                unset($resource['xtags']);
            }
        }

        $description = array_merge($resource, [
            'active' => 1,
            'entities_linked' => $entities_linked,
            'entities_non_linked' => $entities_non_linked
        ]);

        return [
            'type' => 'document',
            'data' => ['description' => $description],
            'collection_id' => Collection::where('name', 'Public Organization Document collection')->first()->id
        ];
    }

    private function createResourceStructure2($resource)
    {
        $entities_linked = [];
        $entities_non_linked = [];
        $array_linked = [];

        /*if (isset($resource->xtags_interlinked)) {
            foreach ($resource->xtags_interlinked as $key => $entity) {
                $entities_linked[] = $this->getInfoXtags($entity, true);
                $array_linked[] = $key;
            }
        }

        if (isset($resource->xtags)) {
            foreach ($resource->xtags as $key => $entity) {
                if (!in_array($key, $array_linked)) {
                    $entities_non_linked[] = $this->getInfoXtags($entity, false);
                }
            }
        }
        $resource->active = 1;
        $resource->entities_linked = $entities_linked;
        $resource->entities_non_linked = $entities_non_linked;*/
   

        return [
            'type' => 'document',
            'data' => ['description' => $resource],
            'collection_id' => Collection::where('name', 'Public Organization Document collection')->first()->id
        ];
    }

    private function getUrl($enhancer)
    {

        if ($enhancer == 'All' || count(explode(',', $enhancer)) > 1) {
            return $this->xowlUrl . '/enhance/all';
        } else {
            return $this->xowlUrl . '/enhance';
        }
    }

    public function fetchDocuments($semanticRequest, $isUuidSearch = false)
    {

        $categories = config('inesja.dataset');

        if (!$isUuidSearch) {
            $keys = key_exists('ids', $semanticRequest) ? explode(',', $semanticRequest['ids']) : [];
        } else {
            $keys = key_exists('uuids', $semanticRequest) ? explode(',', $semanticRequest['uuids']) : [];
        }

        $type = key_exists('type', $semanticRequest) && isset($categories[$semanticRequest['type']]) ? $semanticRequest['type'] : config('inesja.default_type');
        $enhance = key_exists('enhance', $semanticRequest) ? $semanticRequest['enhance'] : '';

        $resourcesInesJA = [];

        foreach ($keys as $key) {
            $document = $this->getSingleDocument($key, $type, $categories[$type]['search']['_source'], $isUuidSearch);
            if (empty($document)) continue;
            $newResource = $this->parseStdClassToResource($document, $categories[$type]['fields']);
            $newResource['category'] = $categories[$type]['category'];
            $newResource['body'] = $this->cleanText($newResource['body']);
            $resourcesInesJA[$newResource['uuid']] = $newResource;
        }

        $errors = [];
        if ($enhance) {
            $this->concurrentPost($resourcesInesJA, $errors, $enhance);
        }

        $resources = [];
        foreach ($resourcesInesJA as $resource) {
            $resources[] = $this->createResourceStructure($resource, $semanticRequest);
        }

        return [
            'resources' => $resources,
            'errors' => $errors
        ];
    }

    public function getSingleDocument($key, $type, $source, $isUuidSearch = false)
    {

        $uri = config('inesja.base_url');
        $uri .= $type . '.json';
        $uri .= '?_source=' . $source;
        if (!$isUuidSearch) {
            $uri .= '&' . config('inesja.search_id') . '"' . $key . '"';
        } else {
            $uri .= '&' . config('inesja.search_uuid') . '"' . $key . '"';
        }


        $response = $this->client->get($uri);
        try {
            $responseBody = json_decode($response->getBody());
        } catch (\Exception $th) {
            throw new \Exception($th->getMessage());
        }

        $field_name_result = config('inesja.field_result');

        $result = [];
        if (property_exists($responseBody, 'numResultados') && $responseBody->numResultados > 0) {
            $result = $responseBody->$field_name_result;
        }

        return $result;
    }

    public function getDataINES($p, $ps, $type)
    {
        $categories = config('inesja.dataset');
        $resources = [];

        if (isset($categories[$type])) $categories = [$categories[$type]];

        foreach ($categories as $category => $data) {
            $uri = config('inesja.base_url');

            $uri .= $category . '.json';                            // dataset and format
            $uri .= '?_source=' . $data['search']['_source'];       // data response
            $uri .= '&size=' . $ps . '&from=' . $p;                 // pagination

            // Other options
            $uri .= '&' . config('inesja.search_sort') . '&' . config('inesja.search_only_published');

            $response = $this->client->get($uri);
            try {
                $responseBody = json_decode($response->getBody());
            } catch (\Exception $th) {
                throw new \Exception($th->getMessage());
            }

            $field_name_result = config('inesja.field_result');

            $results = [];
            if (property_exists($responseBody, 'numResultados') && $responseBody->numResultados > 0) {
                $results = $responseBody->$field_name_result;
            }

            foreach ($results as $resultdata) {
                $newResource = $this->parseStdClassToResource($resultdata, $data['fields']);
                $newResource['category'] = $data['category'];
                $newResource['body'] = $this->cleanText($newResource['body']);
                $resources[$newResource['uuid']] = $newResource;
            }
        }

        return $resources;
    }

    public function getValueOfArray($obj, $keys)
    {
        $output = $obj;
        $keys = explode('**optional**', $keys);
        $array_keys = explode('.', $keys[0]);

        foreach ($array_keys as $key) {
            $output = $output[$key];
        }
        return $output;
    }

    public function parseStdClassToResource($data, $fields)
    {
        $add_url_fields = ['image', 'external_url'];
        $output = [];
        $urlJA = config('inesja.url');
        foreach ($fields as $key => $field) {
            $output[$key] = $data;
            $array_keys = explode('.', $field);
            foreach ($array_keys as $field_key) {
                if (!is_string($output[$key])) {
                    if (is_array($output[$key])) {
                        $output[$key] = $output[$key][0];
                    }
                    $output[$key] = isset($output[$key]->$field_key) ? $output[$key]->$field_key : '';
                }
            }
            foreach ($add_url_fields as $field) {
                if (isset($output[$field]) && '' !== $output[$field] && strpos($output[$field], $urlJA) !== 0) {
                    $separator = strpos($output[$field], '/') !== 0 ? '/' : '';
                    $output[$field] = $urlJA . $separator . $output[$field];
                }
            }
        }
        return $output;
    }

    public function addOnlyResource(&$resourcesInesJA)
    {
        foreach ($resourcesInesJA as $key => $resource) {

            $resourcesInesJA[$key]['enhanced_interactive'] = false; //1 == $params['extra_links'];
            $resourcesInesJA[$key]['enhanced'] = false;
            $resourcesInesJA[$key]['xtags'] = [];
            $resourcesInesJA[$key]['xtags_interlinked'] = [];
            $resourcesInesJA[$key]['request_data'] = [];
        }
    }

    public function concurrentPost(&$resourcesInesJA, &$errors, $enhance, $params = [])
    {
        $promises = [];

        if (count($params) === 0) {
            $options = [
                "watson" => ["features" => ["entities" => ["mentions" => false]], "extra_links" => true],
                "dbpedia" => ["confidence" => 1, "extra_links" => true],
                "comprehend" => ["LanguageCode" => "es", "extra_links" => true],
                "extra_links" => true
            ];

            if (isset($options[strtolower($enhance)])) {
                $options = [
                    $options[strtolower($enhance)]
                ];
                $options['extra_links'] = true;
            }
            $params = [
                'options' => json_encode($options)
            ];

            if ('All' !== $enhance) {
                $params['enhancer'] = $enhance;
            }
        }

        $params['interactive'] = 1;
        $params['extra_links'] = true;

        $options = [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'form_params' => $params,
            'timeout' => 60
        ];

        foreach ($resourcesInesJA as $uuid => $resource) {
            $options['form_params']['text'] = $resource['body'];
            $promises[$uuid] = $this->client->postAsync($this->getUrl($enhance), $options);
        }

        $responses = Utils::settle($promises)->wait();

        foreach ($responses as $key => $response) {
            if ($response['state'] === 'rejected') {
                $errors[$key] = [
                    'id' => $resourcesInesJA[$key]['id'],
                    'uuid' => $resourcesInesJA[$key]['uuid'],
                    'title' => $resourcesInesJA[$key]['title'],
                    'status' => 'FAIL'
                ];
                unset($resourcesInesJA[$key]);
                continue;
            }
            $output_xowl = $response['value']->getBody()->getContents();
            $result = json_decode($output_xowl);
            $resourcesInesJA[$key]['enhanced_interactive'] = true; //1 == $params['extra_links'];
            $resourcesInesJA[$key]['enhanced'] = true;
            $resourcesInesJA[$key]['xtags'] = $result->data->xtags;
            $resourcesInesJA[$key]['xtags_interlinked'] = $result->data->xtags_interlinked;
            $resourcesInesJA[$key]['request_data'] = $result->request;
        }
    }

    public function getDataOwl($data, &$errors, $enhance, $params = [])
    {
        $finalResult = [];
        if (count($params) === 0) {
            $options = [
                "watson" => ["features" => ["entities" => ["mentions" => false]], "extra_links" => true,"confidence" => 1],
                "dbpedia" => ["confidence" => 2, "extra_links" => true],
                "comprehend" => ["LanguageCode" => "es", "extra_links" => true],"confidence" => 1,
                "extra_links" => true
            ];

            if (isset($options[strtolower($enhance)])) {
                $options = [
                    $options[strtolower($enhance)]
                ];
                $options['extra_links'] = true;
            }
            $params = [
                'options' => json_encode($options)
            ];

            if ('All' !== $enhance) {
                $params['enhancer'] = $enhance;
            }
        }

        $params['interactive'] = 1;
        $params['extra_links'] = true;

        $options = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'multipart/form-data'
            ],
            'multipart' => [ ],
            'timeout' => 60
        ];

        if (isset($params['File'])) {
            $file = new \Illuminate\Http\File($params['File'][0]->getRealPath());
            $options['multipart'][] = [
                'name' => 'file',
                'contents' => fopen($file->getPathname(), 'r'),
                'filename' => $params['File'][0]->getClientOriginalName()
            ];
        }else{
            $options['multipart'][] = [
                'name' => 'text',
                'contents' => $data->body
            ];
        }
        $options['multipart'][] = [
            'name' => 'options',
            'contents' => json_encode(["watson" => ["features" => ["entities" => ["mentions" => false]], "extra_links" => true,"confidence" => 1],
            "dbpedia" => ["confidence" => 2, "extra_links" => true],
            "comprehend" => ["LanguageCode" => "es", "extra_links" => true],"confidence" => 1,
            "extra_links" => true])
        ];
        // Add interactive field
        $options['multipart'][] = [
            'name' => 'interactive',
            'contents' => '1'
        ];
        $request = new Request('POST', $this->xowlUrl . '/enhance/all?XDEBUG_SESSION_START=VSCODE');
        $request = $request->withBody(new MultipartStream($options['multipart']));

        $promises[$data->uuid] = $this->client->sendAsync($request);

        $responses = Utils::settle($promises)->wait();

        foreach ($responses as $key => $response) {
            if ($response['state'] === 'rejected') {
                $errors[$key] = [
                    'id' => $data->id,
                    'uuid' => $data->uuid,
                    'title' => $data->title,
                    'status' => 'FAIL'
                ];
                continue;
            }
            $output_xowl = $response['value']->getBody()->getContents();
            $result = json_decode($output_xowl);
        /*    $data->enhanced_interactive = true; 
            $data->enhanced = true;*/
            $xtags = $this->deleteDuplicateXtag($result->data->xtags) ;
            $xtags_interlinked = $this->deleteDuplicateXtag($result->data->xtags_interlinked);
            $xtags = $this->checkNonLinked($xtags_interlinked,$xtags);
            $finalResult['xtags'] = $xtags;
            $finalResult['$xtags_interlinked'] = $xtags_interlinked ;
           // $data->request_data = $result->request;
        }
        return $finalResult;
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

    private function cleanText($text)
    {

        $text = preg_replace_callback("# <(?![/a-z]) | (?<=\s)>(?![a-z]) #i", array($this, 'replaceContent'), $text);
        $text = str_replace("\t", "", $text);
        return strip_tags($text);
    }

    private function replaceContent($item  = null, $item2 = null)
    {
        return str_repeat("", mb_strlen($item[0]));
    }


    /**
     * Delete duplicate xtag.
     *
     * @param array $xtags The array of xtags.
     *
     * @return array The array of xtags without duplicates.
     */
    private function deleteDuplicateXtag($xtags){
        $result = [];
        $aux    = [];
        foreach ($xtags as $xtag ) {
            if(!in_array($xtag->text,$aux)){
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
    private function checkNonLinked(array $linked,array $nonLinked){
        $result = [];
        $linkedTexts = array_map(function($link) {
            return $link->text;
        }, $linked);
        foreach ($nonLinked as $tag) {
           if(!in_array($tag->text,$linkedTexts)) $result[] =  $tag;
        }
        return $result;
    }
}
