<?php

namespace App\Http\Requests\Organization;

use App\Enums\Abilities;
use App\Enums\Roles;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;

class SetOrganizationsToUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        //can't assign the super-admin role
        if ($this->with_role_id == Roles::super_admin_id) {
            return false;
        }

        //this line prevents you from authorizing the request yourself. It can be deleted if this request can be executed by the requester.
        if ($this->user()->id == $this->user_id) {
            return false;
        }

        if ($this->user()->isA(Roles::super_admin)) {
            return true;
        }

        //checks if the user who make the request is related with the organization
        $related_organizations = [];
        foreach ($this->user()->organizations()->get() as $org) {
            $related_organizations[] = (string)$org->id;
        }


        $id_to_set = null;
        if (in_array($this->organization_id, $related_organizations)) {
            $id_to_set = $this->organization_id;
        }

        //checks if the user has permissions to manage the specified entity.
        if ($this->user()->can(Abilities::MANAGE_ORGANIZATION, Organization::find($id_to_set)) && $id_to_set != null) {
            $this->request->set('organization_id', $id_to_set);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'organization_id' => 'required|exists:organizations,id',
            'with_role_id' => 'required|exists:roles,id'
        ];
    }
}
