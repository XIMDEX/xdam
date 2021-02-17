<?php

namespace App\Http\Requests;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\WorkspaceType;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SetRoleAbilitiesOnWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if($this->role_id == 1)
            return false;

        if($this->wo_id == 1)
            return false;

        if(Auth::user()->id == $this->user_id)
            return false;

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if($this->on == 'org') {
            $wo_id_validation = 'required|exists:organizations,id';
        } else {
            $wo_id_validation = 'required|exists:workspaces,id';
        }
        return [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'wo_id' => $wo_id_validation,
            'type' => 'required|in:set,unset',
            'on' => 'required|in:org,wsp',
        ];
    }
}
