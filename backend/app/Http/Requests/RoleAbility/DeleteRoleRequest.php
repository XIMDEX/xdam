<?php

namespace App\Http\Requests\RoleAbility;

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
        //only admin can delete role and roles
        if ($this->user()->isAn(Roles::super_admin) && $this->id > Roles::super_admin_id) {
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
