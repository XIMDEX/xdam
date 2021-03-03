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
        //check if user has the manage-organization ability on the specified organization entity
        return $this->user()->can(Abilities::MANAGE_ORGANIZATION, Organization::find($this->organization_id)) ?? false;
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
