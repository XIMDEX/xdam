<?php

namespace App\Http\Requests;

use App\Enums\Abilities;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;

class UnsetOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //can't unset public organization
        if ($this->organization_id == 1) {
            return false;
        }

        if ($this->user()->can(Abilities::canManageOrganization, Organization::find($this->organization_id))) {
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
            'organization_id' => 'required',
        ];
    }
}