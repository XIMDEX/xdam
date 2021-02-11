<?php


namespace App\Traits;


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
        $data = $result[$fieldName];
        $factory->after(
            function ($factory) use ($fieldName, $data) {
                $resultValidation = $this->validateWithSchema($data, json_decode($this->schema));
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
