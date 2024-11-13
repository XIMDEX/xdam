<?php


namespace App\Services\Solr;


use Exception;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\Log;
use Solarium\Client;
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Take care of administrative tasks with Apache Solr
 * It also collects available connection clients
 * Class SolrConfig
 * @package App\Services\Solr
 */
class SolrConfig
{
    /** @var Client[] $clients */
    private array $clients;
    private array $solrFullConfig;
    private array $solrClientsAlias;

    public function __construct(
        SolrConfigRequirements $solarConfigReq
    ) {
        $this->solrClientsAlias = [];
        $this->solrFullConfig = $solarConfigReq->getFullConfig();
        $this->clients = $this->getSolariumClients();
    }

    /**
     * Returns the connection instances for Solr
     * @return Client[]
     */
    public function getClients(): array
    {
        return $this->clients;
    }

    /**
     * Returns a list of instantiated connection clients
     * @param string $solrVersion
     * @return Client[]
     */
    private function getSolariumClients($solrVersion = null): array
    {
        // create a PSR-18 adapter instance
        $httpClient = new GuzzleHttpClient();
        $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $adapter = new \Solarium\Core\Client\Adapter\Psr18Adapter($httpClient, $factory, $factory);
        //$adapter->setTimeout(100000);
        $eventDispatcher = new EventDispatcher();
        $clients = [];

        foreach ($this->solrFullConfig as $nameCore => $config) {
            $endpointCore = $config['endpoint']['core'];
            $auxCore = $this->getCoreNameVersioned($endpointCore, $this->getCoreVersion($solrVersion));
            $this->appendSolrConfigAlias($endpointCore, $auxCore, $nameCore);

            $solrConfig = [
                'endpoint' => [
                    'localhost' => $config["endpoint"]
                ],
                'schema' => $config["schema"],
                'validator' => $config["validator"],
                'resource' =>  "\\App\\Http\\Resources\\Solr\\" . $config['resource'],
                'classHandler' => "\\App\\Services\\Solr\\CoreHandlers\\" . $config['classHandler']
            ];

            if ($endpointCore !== $auxCore) {
                $solrConfig['endpoint']['localhost']['core'] = $auxCore;
            }
            $clients[$endpointCore] = new Client($adapter, $eventDispatcher, $solrConfig);
        }

        return $clients;
    }

    public function updateSolariumClients($solrVersion): array
    {
        return $this->getSolariumClients($solrVersion);
    }

    /**
     * Add schema fields to a solr schema
     * @param Client $client
     * @param array $fields
     * @return mixed
     */
    private function updateSchema(Client $client, array $fields)
    {
        $query = $client->createSelect();
        $request = $client->createRequest($query);
        $request->setHandler('schema');
        $request->setMethod('POST');
        $rawPostData = '';
        $numItems = count($fields);
        $i = 0;
        foreach ($fields as $key => $field) {
            if (++$i === $numItems) {
                $rawPostData .= '"add-field":' . json_encode($field);
            } else {
                $rawPostData .= '"add-field":' . json_encode($field) . ',';
            }
        }
        $request->setRawData($rawPostData);
        return json_decode($client->executeRequest($request)->getBody(), true);
    }

    /**
     * Get the fields from the schema solr of the current connection
     * @param Client $client
     * @return mixed
     */
    private function getCurrentSchema(Client $client)
    {
        $query = $client->createSelect();
        $request = $client->createRequest($query);
        $request->setHandler('schema');
        $currentSchema = json_decode($client->executeRequest($request)->getBody(), true)['schema'];
        return $currentSchema['fields'];
    }

    /**
     * returns the differences between 2 schemas
     * @param array $currentSchema
     * @param array $newSchema
     * @return array
     */
    private function getSchemaDifferences(array $currentSchema, array $newSchema): array
    {
        foreach ($currentSchema as $index => $field) {
            $currentSchema[$field['name']] = $field;
            unset($currentSchema[$index]);
        }

        $diff = array_diff(array_keys($newSchema), array_keys($currentSchema));

        return array_filter(
            $newSchema,
            function ($key) use ($diff) {
                return in_array($key, $diff);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * It is in charge of going through each client and adding the necessary fields in their Solr schema
     * @return string
     * @throws Exception
     */
    public function install(array $cores, bool $allCores): string
    {
        $schemasUpdated = 0;

        foreach ($this->clients as $client) {
            $found = false;

            if ($allCores) {
                $found = true;
            } else {
                foreach ($cores as $core) {
                    if ($client->getEndpoint()->getOptions()['core'] === $core) {
                        $found = true;
                    }
                }
            }

            if ($found) {
                $this->installCore($client);
                $schemasUpdated++;
            }
        }

        return "$schemasUpdated schemas and cores updated";
    }

    public function installCore($client)
    {
        $configSchema = $client->getOptions()["schema"];

        $currentSchema = $this->getCurrentSchema($client);
        $diffSchema = $this->getSchemaDifferences($currentSchema, (array)$configSchema);
        $this->addFieldType($client);

        if (!empty($diffSchema)) {
            $result = $this->updateSchema($client, $diffSchema);

            if (array_key_exists("error", $result)) {
                throw new Exception(json_encode($result["error"]));
            }
        }
    }

    /**
     * It is in charge of going through each client and deleting all the documents
     * @return string
     */
    public function cleanDocuments(array $excludedCores, $action, $solrVersion): string
    {
        $counter = 0;
        $this->clients = $this->updateSolariumClients($this->getCoreVersion($solrVersion));

        foreach ($this->clients as $client) {
            //exclude cores
            $clientCoreName = $client->getEndpoint()->getOptions()['core'];
            $aliasClient = $this->getClientCoreAlias($clientCoreName);

            $isValid = false;
           /* 
            foreach ($aliasClient[$clientCoreName] as $auxClientCoreName) {
                $isValid = !$isValid && array_key_exists($auxClientCoreName, config('solarium.connections', []));
            }
            if (!$isValid) {
                echo "Client detected not valid for xdam \n";
                continue;
            }*/

            if (in_array($clientCoreName, $excludedCores)) {
                echo "$clientCoreName core excluded from $action \n";
                continue;
            }

            // get an update query instance
            $update = $client->createUpdate();

            // add the delete query and a commit command to the update query
            $update->addDeleteQuery('*:*');
            $update->addCommit();

            // this executes the query and returns the result
            $client->update($update);
            $counter++;
        }

        return "$counter instances has been cleared";
    }

    private function appendSolrConfigAlias($solrCore, $solrCoreAlias, $nameCore)
    {
        if (!array_key_exists($solrCore, $this->solrClientsAlias)) {
            $this->solrClientsAlias[$solrCore] = [];
            $aliasClient = [];
        } else {
            $aliasClient = $this->solrClientsAlias[$solrCore];
        }

        if (!array_key_exists($nameCore, $aliasClient)) {
            $aliasClient[] = $nameCore;
        }

        if (!in_array($solrCore, $aliasClient)) {
            $aliasClient[] = $solrCore;
        }

        if (!in_array($solrCoreAlias, $aliasClient)) {
            $aliasClient[] = $solrCoreAlias;
        }
        $this->solrClientsAlias[$solrCore]= array_unique($aliasClient);
    }

    public function getClientCoreAlias($solrCore)
    {
        return $this->solrClientsAlias;
        foreach ($this->solrClientsAlias as $key => $value) {
            foreach ($value as $item) {
                if ($item === $solrCore) return $key;
            }
        }

        return $solrCore;
    }

    public function addFieldType($client): void
    {
        $query = $client->createSelect();
        $request = $client->createRequest($query);
        $request->setHandler('schema');
        $request->setMethod('POST');

        $json_field_type = file_get_contents(storage_path('solr_core_conf/field_types') . '/text_es_custom.json');

        $out = $this->copyRequiredFiles($client);

        echo "\n BEFORE CONTINUE: \n\n $out \n\n\n If you didn't execute the last command, the installation will fail. \n Continue? [y/N]";

        $answer = fgetc(STDIN);

        if($answer === 'y'){
            echo "... resuming installation \n";
        } else {
            echo "Aborted \n";
            die();
        }

        $request->setRawData($json_field_type);
        $res = json_decode($client->executeRequest($request)->getBody(), true);

        if (array_key_exists('error', $res)) {
            echo "\n Error occurred adding field type in core " . $client->getEndpoint()->getOptions()['core'] . ". Check laravel log. \n";
            Log::error(json_encode($res));
            throw new Exception(json_encode($res['error']));
        }
    }

    public function copyRequiredFiles($client): string
    {
        $opts = $client->getEndpoint()->getOptions();

        $endpoint =
            $opts['scheme'] .
            '://' .
            $opts['host'] .
            ( $opts['port'] ? ':'.$opts['port'] : '' ) .
            '/solr/admin/cores?action=STATUS&core=' . $opts['core'];

        $res = json_decode(file_get_contents($endpoint), true);
        $coreConfigDir = $res['status'][$opts['core']]['instanceDir'];
        $core_files_path = storage_path('solr_core_conf/core_files');
        $core_config_files_path = storage_path('solr_core_conf/core_conf_files');

        return 'For core ' . $opts['core'] . " execute this command to continue the installation: \n sudo cp $core_files_path/* $coreConfigDir && sudo chown -R solr:solr $coreConfigDir && sudo cp $core_config_files_path/* $coreConfigDir/conf && sudo chown -R solr:solr $coreConfigDir/conf \n";
    }

    public function getSolrCores(): array
    {
        $cores = [];

        foreach ($this->clients as $key => $value) {
            $cores[] = $key;
        }

        return $cores;
    }

    public function getCoreVersion($coreVersion)
    {
        $solrVersion = env('SOLR_CORES_VERSION', 'garcia_vaquero');
        $solrVersionUsed = $solrVersion;
        $solrVersionUsed = ($coreVersion !== NULL ? $coreVersion : $solrVersionUsed);
        return $solrVersionUsed;
    }

    public function getCoreNameVersioned($solrCore, $solrVersion = null)
    {
        if (isset($this->solrFullConfig[$solrCore]['endpoint']['core'])) $solrCore = $this->solrFullConfig[$solrCore]['endpoint']['core'];
        $solrVersion = (gettype($solrVersion) === 'array' && count($solrVersion) > 0 ? $solrVersion[0] : $solrVersion);
        if ($solrVersion === null || $solrVersion === '') return $solrCore;
        if (gettype($solrVersion) === 'array' && count($solrVersion) === 0) return $solrCore;
        return $solrCore . '_' . $solrVersion;
    }

    public function getNameCoreConfig($type) {
        $core_name = false;
        foreach ($this->solrFullConfig as $core => $data) {
            if ($data['endpoint']['core'] == $type) {
                $core_name = $core;
            }
        }

        return $core_name ? $core_name : $type;
    }

    public function getSolrFullConfig()
    {
        return $this->solrFullConfig;
    }
}
