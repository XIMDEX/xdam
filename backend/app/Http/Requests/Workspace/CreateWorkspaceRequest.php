<?php

namespace App\Http\Requests\Workspace;

use App\Enums\Abilities;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class CreateWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if($this->user()->can(Abilities::canCreateWorkspace, Workspace::class)) {
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
            'name' => 'required|string|min:3',
            'organization_id' => 'required',
        ];
    }
}
