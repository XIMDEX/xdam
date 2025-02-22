<?php

namespace App\Http\Requests;

use App\Enums\ResourceType;
use BenSampo\Enum\Rules\EnumKey;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'name' => 'required',
            'type' => ['required', new EnumKey(ResourceType::class)],
        ];
    }

    public function validationData()
    {
        $requiredParameters = [
            'name' => $this->name,
        ];

        $this->merge($requiredParameters);

        return array_merge($this->all(), $requiredParameters);
    }

}
