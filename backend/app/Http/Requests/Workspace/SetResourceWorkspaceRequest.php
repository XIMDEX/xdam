<?php

namespace App\Http\Requests\Workspace;

use Exception;
use Illuminate\Foundation\Http\FormRequest;

class SetResourceWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    public function checkResourceWorkspaceChangeData()
    {
        return (isset($this->resource_id) && (isset($this->workspace_id) || isset($this->workspace_name)));
    }
}