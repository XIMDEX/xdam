<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Traits\JsonValidatorTrait;
use BenSampo\Enum\Rules\EnumKey;
use Illuminate\Foundation\Http\FormRequest;

class PatchResourceRequest extends FormRequest
{
    use JsonValidatorTrait;


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
            'type' => [new EnumKey(ResourceType::class)],
            'collection_id' => 'exists:collections,id',
        ];
    }

    public function prepareForValidation()
    {
        $all = $this->all();
        if (array_key_exists('data', $all)) {
            $castedData = json_decode($all['data']);
            return $this->merge(['data' => $castedData])->all();
        }
        return $this->all();
    }

    public function withValidator($factory)
    {
        $all = $this->all();
        if (array_key_exists('data', $all)) {
            $this->throwErrorWithValidator($factory, 'data');
            return $factory;
        }
    }
}