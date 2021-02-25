<?php

namespace App\Http\Requests\Workspace;

use App\Enums\Abilities;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ListWorkspacesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //check if user has the view-workspaces ability on the organization
        if(Auth::user()->can('*')) {
            return true;
        }
        $org = Workspace::find(Auth::user()->selcted_workspace)->organization()->first();
        if ($this->user()->canAny([Abilities::ViewWorkspace, Abilities::ManageWorkspace], $org)) {
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
