<?php

namespace App\Http\Requests\OrganizationWorkspace;

use Illuminate\Foundation\Http\FormRequest;

class SetWorkspaceRequest extends FormRequest
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
            'user_id' => 'string|required',
            'workspace_ids' => 'array|required|min:1',
            'workspace_ids.*' => 'required|distinct|min:0'
        ];
    }
}
