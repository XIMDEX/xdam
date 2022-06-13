<?php

namespace App\Http\Requests\Workspace;

use App\Enums\Abilities;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkspaceByNameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //check if user has the view-workspace ability on the specified entity
        if ($this->user()->can(Abilities::READ_WORKSPACE)) {
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
            'workspace_name' => 'required|string',
            'name' => 'required|string',
            'force' => 'nullable|boolean'
        ];
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['workspace_name'] = $this->route('workspace_name');
        return $data;
    }
}
