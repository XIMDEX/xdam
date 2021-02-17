<?php

namespace App\Http\Requests\Workspace;

use App\Enums\Abilities;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SetWorkspacesToUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if($this->user_id == 1)
            return false;

        $oid_of_wsp = Workspace::find($this->workspace_id)->organization()->first()->id;

        if($this->user()->can(Abilities::canManageWorkspace, Organization::find($oid_of_wsp)))
            return true;

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
            'user_id' => 'required|exists:users,id',
            'workspace_id' => 'required|exists:workspaces,id',
            'with_role_id' => 'required|exists:roles,id'
        ];
    }
}
