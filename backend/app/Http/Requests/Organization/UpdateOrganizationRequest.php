<?php

namespace App\Http\Requests\Organization;

use App\Enums\Abilities;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //checks that the user has permissions to update in the specified entity.
        if ($this->user()->can(Abilities::MANAGE_ORGANIZATION, Organization::find($this->organization_id))) {
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
            'organization_id' => 'required',
            'name' => 'required|min:3|string',
        ];
    }
}
