<?php

declare(strict_types=1);

namespace App\Http\Requests\RequestResource\Book\Unit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLinksRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'isbn' => 'required|string',
            'links' => 'required|array',
            'links.*' => 'required|string',
        ];
    }

    public function validationData()
    {
        return [
            'isbn' => $this->isbn,
            'links' => $this->input('links')
        ];
    }
}
