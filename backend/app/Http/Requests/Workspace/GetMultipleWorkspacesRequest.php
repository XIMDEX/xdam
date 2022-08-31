<?php
declare(strict_types=1);

namespace App\Http\Requests\Workspace;

use App\Enums\Abilities;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GetMultipleWorkspacesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // $user = Auth::user();
        
        // foreach($this->workspacesId as $workspaceId) {
        //     if(!$user->can(Abilities::READ_WORKSPACE, Workspace::find($workspaceId))){
        //         return false;
        //     }
        // }
        
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
            'workspacesId' => 'required|array',
            'workspacesId.*' => 'integer'
        ];
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        return $data;
    }
}
