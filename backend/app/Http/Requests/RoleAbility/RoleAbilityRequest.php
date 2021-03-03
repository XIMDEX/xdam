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
            'role_id'    => 'required',
            'ability_ids' => 'required|array',
            'action' => 'required|in:set,unset'
        ];
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        if (strpos($this->getRequestUri(), '/unset/ability') !== false) {
            $data['action'] = 'unset';
        } else {
            $data['action'] = 'set';
        }
        return $data;
    }
}
