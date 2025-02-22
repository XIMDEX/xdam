<?php

namespace App\Http\Requests\Workspace;

use App\Enums\Abilities;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //authorized my canManageWorkspace middleware
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
            'workspace_id' => 'required',
            'name' => 'string|required|min:3'
        ];
    }
}
