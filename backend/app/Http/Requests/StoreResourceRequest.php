<?php

namespace App\Http\Requests;

use App\Enums\ResourceType;
use App\Traits\JsonValidatorTrait;
use BenSampo\Enum\Rules\EnumKey;
use Illuminate\Foundation\Http\FormRequest;

class StoreResourceRequest extends FormRequest
{
    use JsonValidatorTrait;

    private $schema = '
    {
        "type": "object",
        "properties": {
            "description": {
                "type": "object",
                "required": ["active", "partials"],
                "properties": {
                    "name": {
                        "type": "string",
                        "format": "string"
                    },
                    "external_url": {
                        "type": "string",
                        "format": "string"
                    },
                    "description": {
                        "type": "string",
                        "format": "string"
                    },
                    "tags": {
                        "type": "array",
                        "format": "string"
                    },
                    "categories": {
                        "type": "array",
                        "format": "string"
                    },
                    "partials": {
                        "type": "object",
                        "properties": {
                            "pages": {
                                "type": "integer",
                                "format": "integer"
                            }
                        }
                    },
                    "active": {
                        "type": "boolean"
                    }
                }
            }
        },
        "required": ["description"]
    }';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'Preview' => 'file',
            'type' => ['required', new EnumKey(ResourceType::class)]
        ];
    }

    public function prepareForValidation()
    {
        $all = $this->all();
        $castedData = [];
        if (array_key_exists("data", $all)) {
            $castedData = json_decode($all["data"]);
        }
        return $this->merge(["data" => $castedData])->all();
    }

    public function withValidator($factory)
    {
        $this->throwErrorWithValidator($factory,  "data");
        return $factory;
    }


}
