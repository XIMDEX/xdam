<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SetOrganizationsToUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if($this->with_role_id == 1)
            return false;

        if(Auth::user()->isA('admin'))
            return true;


        //se verifica que el usuario que esta haciendo el request
        //esté relacionado con la/las organizaciones y que su rol sea el adecuado para setear organizaciones

        $enabled_orgs = [];
        foreach (Auth::user()->organizations()->get() as $org) {
            $enabled_orgs[] = (string)$org->id;
        }

        $id_to_set = null;
        if(in_array($this->organization_id, $enabled_orgs)) {
            $id_to_set = $this->organization_id;
        }

        $this->request->set('organization_id', $id_to_set);

        if(!$this->organization_id)
            return false;

        return true;
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
