<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SelectOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //check if user belongs to the requested organization
        if (count($this->user()->organizations()->where('organizations.id', $this->organization_id)->get()) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'organization_id' => 'required|exists:organizations,id'
        ];
    }
}
