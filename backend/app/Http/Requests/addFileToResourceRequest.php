<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use Illuminate\Foundation\Http\FormRequest;

class addFileToResourceRequest extends FormRequest
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
            MediaType::File()->key => 'file',
        ];
    }

    public function validationData()
    {
        $requiredParameters = [
            MediaType::File()->key => $this->file(MediaType::File()->key)
        ];

        $this->merge($requiredParameters);

        return array_merge($this->all(), $requiredParameters);
    }
}
