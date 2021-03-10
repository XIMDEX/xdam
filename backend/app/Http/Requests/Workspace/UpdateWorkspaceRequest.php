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
        //check if user has the update-workspace ability on the specified entity
        if ($this->user()->canAny([Abilities::MANAGE_WORKSPACE, Abilities::UPDATE_WORKSPACE], Workspace::find($this->workspace_id))) {
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
            'workspace_id' => 'required',
            'name' => 'string|required|min:3'
        ];
    }
}
