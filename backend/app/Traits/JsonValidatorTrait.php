<?php


namespace App\Traits;


use App\Models\Collection;
use App\Services\Solr\SolrConfig;
use Illuminate\Support\Facades\App;
use Opis\JsonSchema\Validator;
use phpDocumentor\GraphViz\Exception;

trait JsonValidatorTrait
{
    protected function validateWithSchema($data, $schema)
    {
        $validator = new Validator();
        return $validator->dataValidation($data, $schema);
    }

    protected function throwErrorWithValidator($factory, $fieldName)
    {
        $result = $factory->getData();
        $collection = Collection::findOrFail($result["collection_id"]);
        $solrClients = App::make(SolrConfig::class)->getClients();
        $foundIndex = array_search($collection->solr_connection, array_keys($solrClients));
        if ($foundIndex >= 0)
        {
            $validator = $solrClients[$collection->solr_connection]->getOption("validator");
            $data = $result[$fieldName];
            $factory->after(
                function ($factory) use ($fieldName, $data, $validator) {
                    $resultValidation = $this->validateWithSchema($data, $validator);
                    if ($resultValidation->hasErrors()) {
                        $errors = $resultValidation->getErrors();
                        foreach ($errors as $error) {
                            $factory->errors()->add($fieldName, $error->keywordArgs());
                        }
                    }
                }
            );
            return $factory;
        } else {
            throw new Exception("Client not found for collection $collection->name");
        }

    }
}
