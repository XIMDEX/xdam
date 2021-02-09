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
        if($this->user_id == 1)
            return false;



        $enabled_orgs = [];
        foreach (Auth::user()->organizations()->get() as $org) {
            $enabled_orgs[] = (string)$org->id;
        }
        $ids_to_set = [];
        foreach ($this->organization_ids as $req_wid) {
            if(in_array($req_wid, $enabled_orgs)) {
                $ids_to_set[] = $req_wid;
            }
        }
        $this->request->set('organization_ids', $ids_to_set);

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
            'user_id' => 'string|required',
            'organization_ids' => 'array|required|min:1',
            'organization_ids.*' => 'required|distinct|min:0'
        ];
    }
}
