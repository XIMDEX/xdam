<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use Illuminate\Foundation\Http\FormRequest;

class addPreviewToResourceRequest extends FormRequest
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

    public function rules()
    {
        return [
            MediaType::Preview()->key => 'file',
        ];
    }

    public function validationData()
    {
        $requiredParameters = [
            MediaType::Preview()->key => $this->file(MediaType::Preview()->key)
        ];

        $this->merge($requiredParameters);

        return array_merge($this->all(), $requiredParameters);
    }
}
