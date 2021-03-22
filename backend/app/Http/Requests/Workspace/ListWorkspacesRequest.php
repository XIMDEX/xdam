<?php

namespace App\Http\Requests\Workspace;

use App\Enums\Abilities;
use App\Enums\Roles;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ListWorkspacesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //check if user has the view-workspaces ability on the organization
        $user = Auth::user();

        $org = Organization::find($this->organization_id);
        $this->merge(['org' => $org]);

        if ($user->canAny([Abilities::READ_WORKSPACE, Abilities::MANAGE_ORGANIZATION_WORKSPACES, Abilities::MANAGE_ORGANIZATION], $org) || $user->isA(Roles::SUPER_ADMIN)) {
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
            'organization_id' => 'required|exists:organizations,id'
        ];
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['organization_id'] = $this->route('organization_id');
        return $data;
    }
}
