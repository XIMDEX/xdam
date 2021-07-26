<?php


namespace App\Services\Solr;


use Exception;
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

    public function __construct(
        SolrConfigRequirements $solarConfigReq
    ) {
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
     * @return Client[]
     */
    private function getSolariumClients(): array
    {
        $adapter = new Curl();
        $adapter->setTimeout(0);
        $eventDispatcher = new EventDispatcher();
        $clients = [];
        foreach ($this->solrFullConfig as $config) {
            $solrConfig = [
                'endpoint' => [
                    'localhost' => $config["endpoint"]
                ],
                'schema' => $config["schema"],
                'validator' => $config["validator"],
                'resource' =>  "\\App\\Http\\Resources\\Solr\\" . $config['resource']

            ];
            $clients[$config['endpoint']['core']] = new Client($adapter, $eventDispatcher, $solrConfig);
        }
        return $clients;
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
    public function install(): string
    {
        $schemasUpdated = 0;
        foreach ($this->clients as $client) {
            $configSchema = $client->getOptions()["schema"];
            $currentSchema = $this->getCurrentSchema($client);
            $diffSchema = $this->getSchemaDifferences($currentSchema, (array)$configSchema);
            if (!empty($diffSchema)) {
                $result = $this->updateSchema($client, $diffSchema);
                if (array_key_exists("error", $result)) {
                    throw new Exception($result["error"]);
                }
                $schemasUpdated++;
            }
        }
        return "$schemasUpdated schemas updated";
    }

    /**
     * It is in charge of going through each client and deleting all the documents
     * @return string
     */
    public function cleanDocuments(array $excludedCores, $action): string
    {
        $counter = 0;
        foreach ($this->clients as $client) {
            //exclude cores
            $clientCoreName = $client->getEndpoint()->getOptions()['core'];

            if (!array_key_exists($clientCoreName, config('solarium.connections', []))) {
                echo 'Client detected not valid for xdam' . PHP_EOL;
                continue;
            }

            if (in_array($clientCoreName, $excludedCores)) {
                echo $clientCoreName . ' core excluded from '. $action . PHP_EOL;
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
}
