<?php 



class XowlTextService {
    public function __construct() {}

    public function getDataOwl($data, $enhance, $params = [])
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
        //fin seteo optios
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

        $response = array_shift($responses);
            if ($response['state'] === 'rejected') {
                $finalResult = [
                    'id' => $data->id,
                    'uuid' => $data->uuid,
                    'title' => $data->title,
                    'status' => 'FAIL'
                ];
            }else{
                $output_xowl = $response['value']->getBody()->getContents();
                $result = json_decode($output_xowl);
                $xtags = $this->deleteDuplicateXtag($result->data->xtags) ;
                $xtags_interlinked = $this->deleteDuplicateXtag($result->data->xtags_interlinked);
                $xtags = $this->checkNonLinked($xtags_interlinked,$xtags);
                foreach ($xtags as &$tag) {
                    $tag = $this->getInfoXtags($tag,false);
                }
                foreach ($xtags_interlinked  as &$tag) {
                    $tag = $this->getInfoXtags($tag,true);
                }
                $finalResult['xtags'] = $xtags;
                $finalResult['xtags_interlinked'] = $xtags_interlinked ;
            }
        
        
        return $finalResult;
    }
}