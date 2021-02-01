<?php

namespace App\Http\Requests\RolePermission;

use Illuminate\Foundation\Http\FormRequest;

class RolePermissionRequest extends FormRequest
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
            'role_id' => 'required|string|min:3',
            'permission_id' => 'required|string|min:3'
        ];
    }

    // public function validationData()
    // {
    //     $requiredParameters = [
    //         'name' => $this->data ?? "{}",
    //         'type' => $this->type
    //     ];

    //     $this->merge($requiredParameters);

    //     return array_merge($this->all(), $requiredParameters);
    // }
}
