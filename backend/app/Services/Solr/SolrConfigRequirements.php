<?php


namespace App\Services\Solr;

use App\Utils\Utils;
use Exception;
use Illuminate\Support\Facades\File;

/**
 * Class that checks that the Solr configuration in the project is complete
 * Class SolrConfigRequirements
 * @package App\Services\Solr
 */
class SolrConfigRequirements
{
    const schemaSufix = '_schema.json';
    const validatorSufix = '_validator.json';

    private string $schemaPath;
    private string $validatorPath;
    private array $config;

    public function __construct()
    {
        $this->config = config('solarium.connections', []);
        $this->schemaPath = storage_path(config('solarium.solr_schemas_folder'));
        $this->validatorPath = storage_path(config('solarium.solr_validators_folder'));
    }

    /**
     * If any required file is missing, an exception is thrown
     * @return array
     * @throws Exception
     */
    public function getFullConfig(): array
    {
        foreach ($this->config as $key => $value) {
            $this->config[$key]["validator"] = $this->getValidator($key);
            $this->config[$key]["schema"] = $this->getSchema($key);
        }
        return $this->config;
    }

    public function getValidator($key)
    {
        $validatorPath = $this->validatorPath . "/$key" . self::validatorSufix;

        return Utils::getJsonFile($validatorPath, false, "Validation configuration missing for core $key");
    }

    public function getSchema($key)
    {
        $validatorPath = $this->schemaPath . "/$key" . self::schemaSufix;

        return Utils::getJsonFile($validatorPath, false, "Schema configuration missing for core $key");
    }
}

