<?php

namespace App\Http\Requests\Organization;

use App\Enums\Abilities;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;

class DeleteOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if($this->user()->can(Abilities::canManageOrganization, Organization::find($this->organization_id)) && $this->organization_id != null) {
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
            //
        ];
    }
}
