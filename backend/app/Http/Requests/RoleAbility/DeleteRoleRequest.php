<?php

namespace App\Http\Requests\RoleAbility;

use App\Enums\Abilities;
use App\Enums\Roles;
use Illuminate\Foundation\Http\FormRequest;

class DeleteRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->user()->can(Abilities::MANAGE_ROLES, $this->organization) || $this->user()->can('*')) {
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

        ];
    }
}
