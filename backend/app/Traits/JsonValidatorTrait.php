<?php


namespace App\Traits;


use App\Models\Collection;
use Opis\JsonSchema\Validator;

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
        $collectionType = Collection::findOrFail($result["collection_id"])->type;
        $validator = $collectionType->getValidator();
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
    }
}
