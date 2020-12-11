<?php

namespace App\Http\Requests;

use App\Enums\ResourceType;
use BenSampo\Enum\Rules\EnumKey;
use Illuminate\Foundation\Http\FormRequest;

class GetCatalogueRequest extends FormRequest
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
            'type' => ['required', new EnumKey(ResourceType::class)],
        ];
    }

    public function validationData()
    {
        $requiredParameters = [
            'type' => $this->type,
        ];

        $this->merge($requiredParameters);

        return array_merge($this->all(), $requiredParameters);
    }
}
