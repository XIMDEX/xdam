<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CDNHashResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resource_id' => 'required',
            'collection_id' => 'required',
            'cdn_code' => 'required|exists:cdns',
        ];
    }
}
