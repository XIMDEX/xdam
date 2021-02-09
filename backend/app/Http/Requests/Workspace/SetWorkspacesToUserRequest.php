<?php

namespace App\Http\Requests\Workspace;

use App\Enums\WorkspaceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SetWorkspacesToUserRequest extends FormRequest
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

        $enabled_wsps = [];
        foreach (Auth::user()->workspaces()->get() as $wsp) {
            if($wsp->type == WorkspaceType::personal)
                continue;

            $enabled_wsps[] = (string)$wsp->id;
        }
        $ids_to_set = [];
        foreach ($this->workspace_ids as $req_wid) {
            if(in_array($req_wid, $enabled_wsps)) {
                $ids_to_set[] = $req_wid;
            }
        }
        $this->request->set('workspace_ids', $ids_to_set);
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
            'workspace_ids' => 'array|required|min:1',
            'workspace_ids.*' => 'required|distinct|min:0'
        ];
    }
}
