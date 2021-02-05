<?php

namespace App\Http\Requests\RoleAbility;

use Illuminate\Foundation\Http\FormRequest;

class RoleAbilityRequest extends FormRequest
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
            'role_id'    => 'required|string',
            'ability_id' => 'required_without_all:ability,tile',
            'ability'    => 'required_without_all:ability_id|string|min:3',
            'title'      => 'required_without_all:ability_id|string|min:3',

        ];
    }
}
