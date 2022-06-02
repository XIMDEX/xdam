<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class GetOrganizationFromUuidRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //request protected by middleware can:*

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
            'organization_id' => 'required|exists:organizations,xdir_id'
        ];
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['organization_id'] = $this->route('organization_id');
        return $data;
    }
}
