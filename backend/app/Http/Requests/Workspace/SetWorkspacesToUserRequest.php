<?php

namespace App\Http\Requests\Workspace;

use App\Enums\Abilities;
use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class SetWorkspacesToUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //cannot set super-admin role
        if ($this->with_role_id == Roles::super_admin_id) {
            return false;
        }

        //Checks that the user to set abilities is attached to the organization of the worksapce
        $wsp = Workspace::find($this->workspace_id);
        $usr = User::find($this->user_id);
        if (!$usr->organizations()->get()->contains($wsp->organization()->first()->id)) {
            return false;
        }

        //get the organization of the workspace to set
        $oid_of_wsp = Workspace::find($this->workspace_id)->organization()->first()->id;
        $org = Organization::find($oid_of_wsp);

        //to know if the user who make the request had permissions to set workspaces in the specified organization
        return $this->user()->can(Abilities::MANAGE_ORGANIZATION, $org) ?? false;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (strpos($this->getRequestUri(), '/unset/user') !== false) {
            return [
                'user_id' => 'required|exists:users,id',
                'workspace_id' => 'required|exists:workspaces,id',
            ];
        } else {
            return [
                'user_id' => 'required|exists:users,id',
                'workspace_id' => 'required|exists:workspaces,id',
                'with_role_id' => 'required|exists:roles,id'
            ];
        }
    }
}
