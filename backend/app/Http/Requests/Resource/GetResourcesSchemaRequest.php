<?php

declare(strict_types=1);

namespace App\Http\Requests\Resource;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GetResourcesSchemaRequest extends FormRequest
{
    public function authorize()
    {
        $user = Auth::user();

        if (!isset($user->xdirRoles)) {
            return false;
        }

        return in_array('XDAM_VIEWER', $user->xdirRoles);
    }

    public function rules()
    {
        return [];
    }

}
