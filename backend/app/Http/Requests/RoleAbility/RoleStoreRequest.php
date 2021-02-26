<?php

namespace App\Http\Requests\RoleAbility;

use Illuminate\Foundation\Http\FormRequest;

class RoleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //authorized by manage.organizations middleware
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
            'name' => 'required|string',
            'title' => 'required|string',
            'organization' => 'required'
        ];
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['organization'] = $this->route('organization');
        return $data;
    }
}
