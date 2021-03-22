<?php

namespace App\Http\Requests;

use App\Enums\Abilities;
use App\Enums\WorkspaceType;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AttachResourceToWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = Auth::user();
        $wsp = Workspace::find($user->selected_workspace);

        //check if user is attached to the organization
        $is_user_attached_to_organization = $user->organizations()->get()->contains($wsp->organization()->first()) ? true : false;

        //check if user has a Manager or Editor role in the workspace
        $user_has_permissions = ($user->canAny([Abilities::UPDATE_WORKSPACE, Abilities::MANAGE_WORKSPACE], $wsp) || $wsp->isPublic() ) ? true : false;

        $user_can_manage_organization_of_workspace = $user->canAny([Abilities::MANAGE_ORGANIZATION, Abilities::MANAGE_ORGANIZATION_WORKSPACES], $wsp->organization()->first());
        if($is_user_attached_to_organization && $user_has_permissions || $user_can_manage_organization_of_workspace) {
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
            'resource_id' => 'required|exists:dam_resources,id',
        ];
    }
}
