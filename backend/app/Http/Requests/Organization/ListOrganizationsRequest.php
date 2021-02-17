<?php

namespace App\Http\Requests\Organization;

use App\Enums\Abilities;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;

class ListOrganizationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if($this->user()->can(Abilities::canManageOrganization, Organization::class)) {
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
