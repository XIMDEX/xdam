<?php

namespace App\Http\Requests;

use App\Enums\WorkspaceType;
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
        if($this->workspace_id == 1)
            return false;

        foreach (Auth::user()->workspaces()->get() as $wsp) {
            if($wsp->type == WorkspaceType::personal)
                continue;

            if($this->workspace_id == $wsp->id)
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
            'user_id' => 'required',
            'role_id' => 'required',
            'workspace_id' => 'required',
        ];
    }
}
