<?php

namespace App\Http\Requests;

use App\Enums\Abilities;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class GetDamResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can(Abilities::ReadResourceReport, Workspace::find($this->user()->selected_workspace)) ?? false;
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
            //
        ];
    }
}
