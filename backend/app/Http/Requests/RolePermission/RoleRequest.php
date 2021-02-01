<?php

namespace App\Http\Requests\RolePermission;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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
            'name' => 'required_without_all:name,role_id',
            'role_id' => 'required_without_all:role_id,name',
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
