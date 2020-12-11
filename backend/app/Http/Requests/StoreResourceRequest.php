<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use BenSampo\Enum\Rules\EnumKey;
use Illuminate\Foundation\Http\FormRequest;

class StoreResourceRequest extends FormRequest
{
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
            'data' => 'string',
            MediaType::File()->key => 'file',
            MediaType::Preview()->key => 'file',
            'type' => ['required', new EnumKey(ResourceType::class)]
        ];
    }

    public function validationData()
    {
        $requiredParameters = [
            'data' => $this->data ?? "{}",
            'type' => $this->type
        ];

        $this->merge($requiredParameters);

        return array_merge($this->all(), $requiredParameters);
    }
}
