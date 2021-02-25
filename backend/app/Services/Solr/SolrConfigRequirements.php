<?php


namespace App\Services\Solr;


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
            $validatorPath = $this->validatorPath . "/$key" . self::validatorSufix;
            if (!File::exists($validatorPath)) {
                throw new Exception("Validation configuration missing for core $key");
            } else {
                $this->config[$key]["validator"] = json_decode(file_get_contents($validatorPath));
            }

            $schemaPath = $this->schemaPath . "/$key" . self::schemaSufix;
            if (!File::exists($schemaPath)) {
                throw new Exception("Schema configuration missing for core $key");
            } else {
                $this->config[$key]['schema'] = json_decode(file_get_contents($schemaPath));
            }
        }
        return $this->config;
    }
}
