<?php

namespace App\Http\Requests;


class SetTagsRequest extends ApiRequest
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
            'tags' => 'required|array',
        ];
    }

    public function messages()
    {
        return [
            'tags.required' => 'A key tags is required',
        ];
    }
}
