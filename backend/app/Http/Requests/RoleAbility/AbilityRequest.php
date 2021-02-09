<?php

namespace App\Http\Requests\RoleAbility;

use Illuminate\Foundation\Http\FormRequest;

class AbilityRequest extends FormRequest
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
            'name' => 'required|string|min:3',
            'title' => 'required|string|min:3'
        ];
    }
}
