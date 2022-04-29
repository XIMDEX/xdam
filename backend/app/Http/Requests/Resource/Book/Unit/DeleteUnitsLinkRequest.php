<?php

declare(strict_types=1);

namespace App\Http\Requests\Resource\Book\Unit;

use Illuminate\Foundation\Http\FormRequest;

class DeleteUnitsLinkRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'isbn' => 'required|string',
            'units' => 'required|array',
            'units.*' => 'required|int',
        ];
    }

    public function validationData()
    {
        return [
            'isbn' => $this->isbn,
            'units' => $this->input('units')
        ];
    }
}
