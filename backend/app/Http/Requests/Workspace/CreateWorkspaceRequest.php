<?php

namespace App\Http\Requests\Workspace;

use App\Enums\Abilities;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if($this->user()->can(Abilities::canManageOrganization, Organization::find($this->organization_id)))
            return true;
        // if(count($this->user()->organizations()->where('organizations.id', $this->organization_id)->get()) > 0 || $this->user()->isA('admin')) {
        //     return true;
        // }
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
            'name' => 'required|string|min:3',
            'organization_id' => 'required',
        ];
    }
}
