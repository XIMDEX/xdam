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
            'Preview' => 'file',
            'data' => 'string',
            'type' => ['required', new EnumKey(ResourceType::class)]
        ];
    }

}
