<?php

namespace App\Http\Requests;

use App\Enums\Abilities;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SelectWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //if null, user is working in personal context
        if($this->workspace_id == null || Auth::user()->can('*')) {
            return true;
        }
        //check if the user belongs to the requested workspace
        $wsp = Workspace::find($this->workspace_id);
        $user_can_manage_organization_of_workspace = $this->user()->can(Abilities::MANAGE_ORGANIZATION, $wsp->organization()->first());

        if (count($this->user()->workspaces()->where('workspaces.id', $this->workspace_id)->get()) > 0 || $user_can_manage_organization_of_workspace) {
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

        ];
    }
}
