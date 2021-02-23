<?php

namespace App\Http\Requests;

use App\Enums\Roles;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
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
        //request protected by middleware 'manage-roles'
        if ($this->role_id == Roles::admin) {
            return false;
        }

        if ($this->wo_id == 1) {
            return false;
        }

        if ($this->user()->id == $this->user_id) {
            return false;
        }


        //Checks if the user to set abilities is attached to the organization and/or worksapce
        $usr = User::find($this->user_id);
        if ($this->on == 'wsp') {
            $wsp = Workspace::find($this->wo_id);
            if (!$usr->organizations()->get()->contains($wsp->organization()->first()->id)) {
                return false;
            }
        } else {
            $org = Organization::find($this->wo_id);
            if (!$usr->organizations()->get()->contains($org->id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->on == 'org') {
            $wo_id_validation = 'required|exists:organizations,id';
        } else {
            $wo_id_validation = 'required|exists:workspaces,id';
        }
        //'in' validator only accepts defined values. In this case: set,unset
        return [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'wo_id' => $wo_id_validation,
            'type' => 'required|in:set,unset',
            'on' => 'required|in:org,wsp',
        ];
    }
}
