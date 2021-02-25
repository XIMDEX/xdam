<?php

namespace App\Http\Requests\Workspace;

use App\Enums\Abilities;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class GetWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //check if user has the view-workspace ability on the specified entity
        if ($this->user()->can(Abilities::ViewWorkspace, Workspace::find($this->workspace_id))) {
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
